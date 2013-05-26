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
        
        $registered = $this->isWhoIsEntryFound($result);
        $message = $registered ? 'Domain is registered' : 'Domain seems to be free';
        
        return self::successResponse(
            $message,
            array(
                'registered' => $registered,
                'whois' => utf8_encode($result),
            )
        );
    }
    
    /**
     * 
     * @param string $media
     * @param string $name
     * @return JsonResponse
     */
    public function checkSocialMediaAction($media, $name)
    {
        try {
            switch (strtolower($media)){
                case 'twitter':
                    return $this->checkTwitter($name);
                case 'github':
                    return $this->checkGithub($name);
                case 'facebook':
                    return $this->checkFacebook($name);
                default:
                    throw new \InvalidArgumentException("Unsupported media");
            }
        } catch (\Exception $e){
            return self::failureResponse(
                'Unable to check social media: '. $e->getMessage()
            );
        }
    }
    
    /**
     * Checks if given twitter account exists or not
     * @param string $name
     */
    protected function checkTwitter($name)
    {
        $url = sprintf('https://twitter.com/%s', $name);
        $content = '';
        try {
            $content = file_get_contents($url);
            $registered = true;
        } catch (\Exception $e) {
            $registered = false;
        }
        
        if ( empty ($content) || ! $registered) {
            $registered = false;
        }
        
        return self::socialMediaResponse($registered, $url);
    }
    
    /**
     * Checks if given twitter account exists or not
     * @param string $name
     */
    protected function checkFacebook($name)
    {
        $url = sprintf('https://facebook.com/%s', $name);
        try {
            $content = file_get_contents($url);
            $registered = true;
        } catch (\Exception $e) {
            $registered = false;
        }
        
        if ( empty ($content) || ! $registered) {
            $registered = false;
        }
        
        return self::socialMediaResponse($registered, $url);
    }
    
    /**
     * Checks if given twitter account exists or not
     * @param string $name
     */
    protected function checkGithub($name)
    {
        $url = sprintf('https://github.com/%s', $name);
        try {
            $content = file_get_contents($url);
            $registered = true;
        } catch (\Exception $e) {
            $registered = false;
        }
        
        if ( empty ($content) || ! $registered) {
            $registered = false;
        }
        
        return self::socialMediaResponse($registered, $url);
    }
    
    protected static function socialMediaResponse($registered, $link)
    {
        return self::successResponse(
           $registered 
                ? 'Account seems to be registered' 
                : 'Account seems to be free', 
            array(
                'registered' => $registered,
                'link' => $link
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
    
    private static function isWhoIsEntryFound($whoIsResult)
    {
        $nots = array(
            'not (entries|registered|found)',
            'no (entries|data) found',
            'status:\s+(available|free)',
            ' \- Available',
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
