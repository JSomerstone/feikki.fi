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
        $message = $registered ? 'Domain seems to be registered' : 'Domain seems to be free';

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
                case 'linkedin':
                    return $this->checkLinkedIn($name);
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
        $url = sprintf('https://api.twitter.com/1/users/lookup.json?screen_name=%s&include_entities=false', $name);
        $link = 'https://twitter.com/signup';
        $message = null;
        try {
            $content = json_decode(file_get_contents($url));
        } catch (\Exception $e) {
            return self::socialMediaResponse(false, $link);
        }

        if ( ! empty($content)) {
            $registered = true;
            $result = current($content);
            $link = sprintf('https://twitter.com/%s', $result->screen_name);
            $message = sprintf(
                '%s (@%s)',
                $result->name,
                $result->screen_name
            );
        }
        else {
            $registered = false;
        }
        return self::socialMediaResponse($registered, $link, $message);
    }

    /**
     * Checks if given twitter account exists or not
     * @param string $name
     */
    protected function checkFacebook($name)
    {
        $url = sprintf('https://graph.facebook.com/search?q=%s&type=page', $name);
        $message = null;
        try {
            $content = json_decode(file_get_contents($url));
        } catch (\Exception $e) {
            return self::failureResponse('Error occured ' . $e->getMessage());
        }

        if ($content && ! empty($content->data)) {
            $registered = true;
            $result = current($content->data);
            $link = sprintf('https://facebook.com/%d', $result->id);
            $message = sprintf('%s (%s)', $result->name, $result->category);
        }
        else {
            $registered = false;
            $link = 'https://facebook.com/r.php';
        }

        return self::socialMediaResponse($registered, $link, $message);
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
            $url = 'https://github.com/';
        }

        return self::socialMediaResponse($registered, $url);
    }

    /**
     * Checks if given twitter account exists or not
     * @param string $name
     */
    protected function checkLinkedIn($name)
    {
        $url = sprintf('http://www.linkedin.com/ta/federator?query=%s&types=company', $name);
        $registered = false;
        $message = null;

        try {
            $content = json_decode(file_get_contents($url));
        } catch (\Exception $e) {
            return self::failureResponse('Error occured ' . $e->getMessage());
        }

        if (isset($content->company) && !empty($content->company->resultList)) {
            $registered = true;
            $result = current($content->company->resultList);
            $link = $result->url;
            $message = sprintf('%s (%s)', $result->displayName, $result->subLine);
        } else {
            $registered = false;
            $link = 'https://www.linkedin.com/reg/join';
        }

        return self::socialMediaResponse($registered, $link, $message);
    }

    protected static function socialMediaResponse($registered, $link, $message = null)
    {
        if ($message){
            //
        } else {
            $message = $registered
                ? 'Account seems to be registered'
                : 'Account seems to be free';
        }
        return self::successResponse(
           $message,
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
