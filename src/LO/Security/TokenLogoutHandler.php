<?php
/**
 * Created by IntelliJ IDEA.
 * User: samoilenko
 * Date: 3/22/15
 * Time: 3:00 PM
 */

namespace LO\Security;

use LO\Application;
use LO\Model\Entity\Token;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Http\Logout\LogoutHandlerInterface;

/**
 * Handler for clearing invalidating the current session.
 *
 * @author Johannes M. Schmitt <schmittjoh@gmail.com>
 */
class TokenLogoutHandler implements LogoutHandlerInterface{
    /**
     * @var \LO\Application
     */
    private $app;

    public function __construct(Application $app){
        $this->app = $app;
    }

    public function logout(Request $request, Response $response, TokenInterface $token){
        # remove my hash
        $this->app->getEntityManager()
             ->getRepository(Token::class)
             ->createQueryBuilder('t')
             ->delete()
             ->where('t.hash = :hash')
             ->andWhere('t.user_id = :userId')
             ->setParameters([
                 'hash'   => $request->headers->get(ApiKeyAuthenticator::PARAM_NAME),
                 'userId' => $token->getUser()->getId(),
             ])
            ->getQuery()
            ->execute();
        ;

        $max = 1000;
        if(mt_rand(1, $max) < $max / 3){
            # remove all where hash expired
            $this->app->getEntityManager()
                ->getRepository(Token::class)
                ->createQueryBuilder('t')
                ->delete()
                ->where('t.expiration_time <= :expTime')
                ->setParameters([
                    'expTime'   => new \DateTime(),
                ])
                ->getQuery()
                ->execute();
            ;
        }
    }
}
