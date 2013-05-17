<?php

namespace WhatsUp\QueryBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use SclWhois\DomainLookup;
use SclSocket\Socket;

class BackendController extends Controller
{
    /**
     * Makes WHOIS lookup for given domain.
     * Returns JsonResponse with following indexes:
     *      - success (bool), True on successfull lookup, false on error
     *      - message (string), Describing successfullness of lookup
     *      - registred (bool), If the domain is registered or not
     *              (Best quess from the response)
     *      - whois (string), The response to WHOIS lookup
     *      
     * @param string $domain
     * @return JsonResponse
     */
    public function checkDomainAction($domain)
    {
        try {
            $whoIs = new DomainLookup( new Socket() );
            $result = $whoIs->lookup($domain);
        } catch (\Exception $e) {
            return self::failureResponse($e->getMessage());
        }
        
        return self::successResponse(
            'Lookup succeeded',
            array(
                'registred' => $this->isFound($result),
                'whois' => $result,
            )
        );
    }
    
    protected static function failureResponse($message)
    {
        return new JsonResponse(
            array(
                'success' => false,
                'message' => $message
            )
        );
    }
    
    protected static function successResponse($message, $additionalData = array())
    {
        $response = array(
            'success' => true,
            'message' => $message
        );
        
        $content = array_merge($response, $additionalData);
        
        return new JsonResponse($content);
    }
    
    protected static function isFound($whoIsResult)
    {
        $nots = array(
            'not (entries|registered|found)',
            'no (entries|data) found',
            'available',
            'no match',
            'no records matching',
            'nothing found',
            'does not exist',
        );
        
        $regexp = sprintf('/(%s)/i', implode(')|(', $nots));
        
        return preg_match(
            $regexp, 
            $whoIsResult
        ) ? false : true;
    }
}
