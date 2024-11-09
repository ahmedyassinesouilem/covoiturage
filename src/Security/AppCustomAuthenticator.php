<?php
namespace App\Security;

use App\Entity\User;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Authenticator\AbstractLoginFormAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\CsrfTokenBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\RememberMeBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Credentials\PasswordCredentials;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\SecurityRequestAttributes;
use Symfony\Component\Security\Http\Util\TargetPathTrait;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class AppCustomAuthenticator extends AbstractLoginFormAuthenticator
{
    use TargetPathTrait;

    public const LOGIN_ROUTE = 'app_login';

    private UserProviderInterface $userProvider;

    public function __construct(
        private UrlGeneratorInterface $urlGenerator,
        UserProviderInterface $userProvider // Inject the UserProviderInterface
    ) {
        $this->userProvider = $userProvider;
    }

    public function authenticate(Request $request): Passport
    {
        $email = $request->getPayload()->getString('email');

        $request->getSession()->set(SecurityRequestAttributes::LAST_USERNAME, $email);

        // Load the user by email
        $user = $this->userProvider->loadUserByIdentifier($email);

        // Check if the user is validated
        if ($user instanceof User) {
            $this->checkUserIsValidated($user);  // Throws an exception if not validated
        }

        return new Passport(
            new UserBadge($email),
            new PasswordCredentials($request->getPayload()->getString('password')),
            [
                new CsrfTokenBadge('authenticate', $request->getPayload()->getString('_csrf_token')),
                new RememberMeBadge(),
            ]
        );
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): Response
    {
        if ($targetPath = $this->getTargetPath($request->getSession(), $firewallName)) {
            return new RedirectResponse($targetPath);
        }

        return new RedirectResponse($this->urlGenerator->generate('app_home'));
    }

    protected function getLoginUrl(Request $request): string
    {
        return $this->urlGenerator->generate(self::LOGIN_ROUTE);
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): Response
    {
        // Set the error message in the session using the request object
        $request->getSession()->getFlashBag()->add('error', 'Authentication failed: ' . $exception->getMessage());

        // Redirect to the login page
        return new RedirectResponse($this->getLoginUrl($request));
    }

    public function checkUserIsValidated(User $user): void
    {
        if (!$user->isValidated()) {
            throw new AuthenticationException('Your account has not been validated yet. Please wait for administrator approval.');
        }
    }
}
