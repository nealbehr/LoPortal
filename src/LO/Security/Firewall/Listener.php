<?php
namespace LO\Security\Firewall;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\Security\Http\Firewall\ListenerInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\Security\Core\Authentication\AuthenticationManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use LO\Security\Token\UserToken;
use LO\Security\User;
use LO\Core\Token;

class Listener implements ListenerInterface
{
    protected $securityContext;
    protected $authenticationManager;

    private $defaultUserToken = '';

    public function __construct(SecurityContextInterface $securityContext, AuthenticationManagerInterface $authenticationManager
    )
    {
        $this->securityContext       = $securityContext;
        $this->authenticationManager = $authenticationManager;
    }

    /**
     * @param Request $request
     * @return string
     */
    private function getAccessToken(Request $request){
        if(!is_null($request->get('access_token'))){
            return (string)$request->get('access_token');
        }

        /** @var \Symfony\Component\HttpFoundation\ParameterBag $cookie */
        $cookie = $request->cookies;
        if(!is_null($cookie->get('access_token'))){
            return $cookie->get('access_token');
        }

        return $this->defaultUserToken;
    }

    private function getUser(Request $request){
        $accessToken = $this->getAccessToken($request);

        if(is_null($accessToken)){
            return $this->defaultUserToken;
        }

        return $accessToken;

        $userId = substr($accessToken, 0, strpos($accessToken, Token::SEPARATOR_ACCESS_TOKEN));

        if(empty($userId)){
            return $this->defaultUserToken;
        }
        
        $userFromCache = $this->getMemcache()->get(User::getMemcacheKey($userId));

        if(!$userFromCache){
            return $accessToken;
        }

        $user = new User();
        
        return $user->setFromArray($userFromCache);
    }

    public function handle(GetResponseEvent $event){
        $token = new UserToken();

        $user = $this->getUser($event->getRequest());
        
        $token->setUser($user);

        try {
            $authToken = $this->authenticationManager->authenticate($token);
            $this->securityContext->setToken($authToken);

            return;
        } catch (AuthenticationException $failed) {
            // ... you might log something here

            // To deny the authentication clear the token. This will redirect to the login page.
            // Make sure to only clear your token, not those of other authentication listeners.
            // $token = $this->securityContext->getToken();
            // if ($token instanceof WsseUserToken && $this->providerKey === $token->getProviderKey()) {
            //     $this->securityContext->setToken(null);
            // }
            // return;
//            $response = new RedirectResponse($this->loginPath, 302);



            $event->setResponse(new JsonResponse(['message' => 'Auth failed'], Response::HTTP_FORBIDDEN));
        }

        // By default deny authorization
        /*$response = new Response();
        $response->setStatusCode(Response::HTTP_FORBIDDEN);
        $event->setResponse($response);*/
    }
}