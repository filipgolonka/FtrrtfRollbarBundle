<?php

namespace Ftrrtf\RollbarBundle\Twig;

use Ftrrtf\RollbarBundle\Helper\UserHelper;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class RollbarExtension
 *
 * @package Ftrrtf\RollbarBundle\Twig
 */
class RollbarExtension extends \Twig_Extension
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var array
     */
    protected $notifierOptions;

    /**
     * @var array
     */
    protected $environmentOptions;

    /**
     * @var UserHelper
     */
    private $userHelper;

    /**
     * @param ContainerInterface $container
     * @param            $notifierOptions
     * @param            $environmentOptions
     * @param UserHelper $userHelper
     */
    public function __construct(
        ContainerInterface $container,
        $notifierOptions,
        $environmentOptions,
        UserHelper $userHelper
    ) {
        $this->container          = $container;
        $this->notifierOptions    = $notifierOptions;
        $this->environmentOptions = $environmentOptions;
        $this->userHelper         = $userHelper;
    }

    /**
     * Returns a list of functions to add to the existing list.
     *
     * @return array An array of functions
     */
    public function getFunctions()
    {
        return array(
            'rollbarjs' => new \Twig_Function_Method(
                $this,
                'getInitRollbarCode',
                array(
                    'needs_context' => true,
                    'is_safe' => array('html')
                )
            )
        );
    }

    /**
     * @param array $context
     *
     * @return string
     */
    public function getInitRollbarCode(array $context)
    {
        $accessToken = $this->notifierOptions['access_token'];

        $config = array(
            'accessToken' => $accessToken,
            'captureUncaught' => true,
            'payload' => array(
                'environment' => $this->environmentOptions['environment']
            )
        );

        $user = $context['app']->getUser();
        if (isset($user)) {
            $config['payload']['person'] = $this->userHelper->buildUserData($user);
        }

        if ($this->notifierOptions['source_map_enabled']) {
            $config['payload']['client'] = array(
                'javascript' => array(
                    'source_map_enabled' => $this->notifierOptions['source_map_enabled'],
                    'code_version' => $this->notifierOptions['code_version'],
                    'guess_uncaught_frames' => $this->notifierOptions['guess_uncaught_frames']
                )
            );
        }

        $config = json_encode($config);

        $checkIgnoreConfig = $this->getCheckIgnoreConfig();

        return <<<END_HTML
<script>
var _rollbarConfig = {$config};
!function(r){function t(e){if(o[e])return o[e].exports;var n=o[e]={exports:{},id:e,loaded:!1};return r[e].call(n.exports,n,n.exports,t),n.loaded=!0,n.exports}var o={};return t.m=r,t.c=o,t.p="",t(0)}([function(r,t,o){"use strict";var e=o(1).Rollbar,n=o(2),a="https://d37gvrvc0wt4s1.cloudfront.net/js/v1.4/rollbar.min.js";_rollbarConfig.rollbarJsUrl=_rollbarConfig.rollbarJsUrl||a;var i=e.init(window,_rollbarConfig),l=n(i,_rollbarConfig);i.loadFull(window,document,!1,_rollbarConfig,l)},function(r,t){"use strict";function o(){var r=window.console;r&&"function"==typeof r.log&&r.log.apply(r,arguments)}function e(r){this.shimId=++u,this.notifier=null,this.parentShim=r,this.logger=o,this._rollbarOldOnError=null}function n(r,t,o){window._rollbarWrappedError&&(o[4]||(o[4]=window._rollbarWrappedError),o[5]||(o[5]=window._rollbarWrappedError._rollbarContext),window._rollbarWrappedError=null),r.uncaughtError.apply(r,o),t&&t.apply(window,o)}function a(r){var t=e;return l(function(){if(this.notifier)return this.notifier[r].apply(this.notifier,arguments);var o=this,e="scope"===r;e&&(o=new t(this));var n=Array.prototype.slice.call(arguments,0),a={shim:o,method:r,args:n,ts:new Date};return window._rollbarShimQueue.push(a),e?o:void 0})}function i(r,t){if(t.hasOwnProperty&&t.hasOwnProperty("addEventListener")){var o=t.addEventListener;t.addEventListener=function(t,e,n){o.call(this,t,r.wrap(e),n)};var e=t.removeEventListener;t.removeEventListener=function(r,t,o){e.call(this,r,t&&t._wrapped?t._wrapped:t,o)}}}function l(r,t){return t=t||o,function(){try{return r.apply(this,arguments)}catch(o){t("Rollbar internal error:",o)}}}var u=0;e.init=function(r,t){var o=t.globalAlias||"Rollbar";if("object"==typeof r[o])return r[o];r._rollbarShimQueue=[],r._rollbarWrappedError=null,t=t||{};var a=new e;return l(function(){if(a.configure(t),t.captureUncaught){a._rollbarOldOnError=r.onerror,r.onerror=function(){var r=Array.prototype.slice.call(arguments,0);n(a,a._rollbarOldOnError,r)};var e,l,u="EventTarget,Window,Node,ApplicationCache,AudioTrackList,ChannelMergerNode,CryptoOperation,EventSource,FileReader,HTMLUnknownElement,IDBDatabase,IDBRequest,IDBTransaction,KeyOperation,MediaController,MessagePort,ModalWindow,Notification,SVGElementInstance,Screen,TextTrack,TextTrackCue,TextTrackList,WebSocket,WebSocketWorker,Worker,XMLHttpRequest,XMLHttpRequestEventTarget,XMLHttpRequestUpload".split(",");for(e=0;e<u.length;++e)l=u[e],r[l]&&r[l].prototype&&i(a,r[l].prototype)}return r[o]=a,a},a.logger)()},e.prototype.loadFull=function(r,t,o,e,n){var a=function(){var t;if(void 0===r._rollbarPayloadQueue){var o,e,a,i;for(t=new Error("rollbar.js did not load");o=r._rollbarShimQueue.shift();)for(a=o.args,i=0;i<a.length;++i)if(e=a[i],"function"==typeof e){e(t);break}}"function"==typeof n&&n(t)},i=t.createElement("script"),u=t.getElementsByTagName("script")[0];i.src=e.rollbarJsUrl,i.async=!o,i.onload=l(a,this.logger),u.parentNode.insertBefore(i,u)},e.prototype.wrap=function(r,t){try{var o;if(o="function"==typeof t?t:function(){return t||{}},"function"!=typeof r)return r;if(r._isWrap)return r;if(!r._wrapped){r._wrapped=function(){try{return r.apply(this,arguments)}catch(t){throw t._rollbarContext=o()||{},t._rollbarContext._wrappedSource=r.toString(),window._rollbarWrappedError=t,t}},r._wrapped._isWrap=!0;for(var e in r)r.hasOwnProperty(e)&&(r._wrapped[e]=r[e])}return r._wrapped}catch(n){return r}};for(var s="log,debug,info,warn,warning,error,critical,global,configure,scope,uncaughtError".split(","),p=0;p<s.length;++p)e.prototype[s[p]]=a(s[p]);r.exports={Rollbar:e,_rollbarWindowOnError:n}},function(r,t){"use strict";r.exports=function(r,t){return function(o){if(!o&&!window._rollbarInitialized){var e=window.RollbarNotifier,n=t||{},a=n.globalAlias||"Rollbar",i=window.Rollbar.init(n,r);i._processShimQueue(window._rollbarShimQueue||[]),window[a]=i,window._rollbarInitialized=!0,e.processPayloads()}}}}]);
{$checkIgnoreConfig}
</script>
END_HTML;
    }

    /**
     * @return string
     */
    protected function getCheckIgnoreConfig()
    {
        $allowedHosts = [];
        if (
            isset($this->notifierOptions['allowed_js_hosts']) &&
            is_array($this->notifierOptions['allowed_js_hosts'])
        ) {
            $allowedHosts = $this->notifierOptions['allowed_js_hosts'];
        }

        $allowedHosts = json_encode($allowedHosts);

        return <<<END_HTML
(function(Rollbar) {
    var allowedHosts = {$allowedHosts};
    if (allowedHosts.length === 0) {
        allowedHosts.push(window.location.origin);
    }

    function isFromAllowedHosts(filename) {
        for (var i = 0; i < allowedHosts.length; i++) {
            if (filename.match(allowedHosts[i])) {
                return true;
            }
        }

        return false;
    }

    function ignoreRemoteUncaught(isUncaught, args, payload) {
        try {
            var filename = payload.data.body.trace.frames[0].filename;
            if (isUncaught && !isFromAllowedHosts(filename)) {
                return true;
            }
        } catch (e) {
            // Most likely there was no filename or the frame doesn't exist.
        }

        return false;
    }

    Rollbar.configure({checkIgnore: ignoreRemoteUncaught});
})(Rollbar);
END_HTML;

    }

    /**
     * Returns the name of the extension.
     *
     * @return string The extension name
     */
    public function getName()
    {
        return 'ftrrtf_rollbar';
    }
}
