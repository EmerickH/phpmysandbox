<?php
/**
 * phpMySandBox - Simple Database Framework in PHP
 *
 * MVC handling library class and factory.
 * 
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License', or
 * ('at your option) any later version. 
 * (Roman Travé <roman.trave@gmail.com>, 2017)
 *
 * @package    phpMySandBox
 * @subpackage Libraries\Core
 * @license    http://opensource.org/licenses/gpl-license.php  GNU Public License
 * @author     Roman Travé <roman.trave@gmail.com>
 */


// No direct access.
defined('_MySBEXEC') or die;

define('MYSB_RENDER_HTML', 0);
define('MYSB_RENDER_BLANK', 1);

/**
 * Application class.
 *
 * @package    phpMySandBox
 * @subpackage Libraries\Core
 */
class MySBRender extends MySBLog {

    /**
     * @var         bool         true for overlay display.
     */
    public $overlay = false;

    /**
     * @var         bool         true for hidelay display.
     */
    public $hidelay = false;

    /**
     * @var         bool         true for itemlay display.
     */
    public $itemlay = false;

    /**
     * @var         bool         true for blank display.
     */
    public $blanklay = false;

    /**
     * @var         array           Optional local custom header lines
     */
    public $custom_headers = array();

    /**
     * @var         bool         true for blank display.
     */
    public $refresh_time = 0;

    /**
     * @var         bool         true for blank display.
     */
    public $show_topmenu = true;

    /**
     * @var         array           Optional queries logger
     */
    public $content = array();
    
    /**
     * @var         string          Notifications to user
     */
    public $Messages = '';

    /**
     * @var         string          Alerts to user
     */
    public $Alerts = '';


    /**
     * App constructor.
     */
    public function __construct() {

        global $_GET;
        parent::__construct();
        if( (isset($_GET['overlay']) and $_GET['overlay']==1 ) )
            $this->overlay = true;
        if( (isset($_GET['hidelay']) and $_GET['hidelay']==1 ) )
            $this->hidelay = true;
        if( (isset($_GET['itemlay']) and $_GET['itemlay']==1 ) )
            $this->itemlay = true;
        if( (isset($_GET['blanklay']) and $_GET['blanklay']==1 ) )
            $this->blanklay = true;
    }

    /**
     * Store a data in 
     * @param   string  $name       name of the template (mytpl for templates/mytpl.php)
     * @param   string  $module     module containing the template
     * @param   string  $log        log of the *_process* par loading
     */
    public static function setData($name,$value,$template='main') {
        global $app;
        if(!isset($this->$template))
            $this->$template = array();
        $this->$template[$name] = $value;
    }

    /**
     * Load a customisable template path
     * @param   string  $name       name of the template (mytpl for templates/mytpl.php)
     * @param   string  $module     module containing the template
     * @param   string  $log        log of the *_process* par loading
     */
    public static function loadTemplate($name,$module='',$log=true) {
        global $app;
        if($module=='') $prefix = 'core';
        else $prefix = $module;
        $name = str_replace( '.', '', $name );

        // custom file (custom/*_*.php eg.:custom/core_admin/users.php)
        $l_file = MySB_ROOTPATH.'/custom/'.$prefix.'_'.$name.'.php';
        //echo $l_file.'<br>';
        if(file_exists($l_file)) { include ($l_file); return true; };

        // initial file
        if($module!='') {   // module file (modules/$module/templates/*.php)
            $l_file = MySB_ROOTPATH.'/modules/'.$module.'/templates/'.$name.'.php';
        } else {            // file (templates/*.php)
            $l_file = MySB_ROOTPATH.'/templates/'.$name.'.php';
        }
        //echo $l_file.'<br>';
        if(file_exists($l_file)) { include ($l_file); return true; }

        if( $log==true ) {
            $app->LOG("MySBRender::loadTemplate($name,$module): template not found");
            $app->pushAlert("Fatal: template <i>$name</i> in module <i>$module</i> not found!");
        }
        return false;
    }

    /**
     * Load a customisable include path
     * @param   string  $name       name of the include (myinc for includes/myinc.php)
     * @param   string  $module     module containing the include
     * @param   string  $log        log of the *_process* par loading
     */
    public static function loadInclude($name,$module='',$log=true) {
        global $app;
        if($module=='') $prefix = 'core_includes';
        else $prefix = $module.'_includes';
        $name = str_replace( '.', '', $name );

        // custom file (custom/*_*.php eg.:custom/core_include/user_display.php)
        $l_file = MySB_ROOTPATH.'/custom/'.$prefix.'/'.$name.'.php';
        //echo $l_file.'<br>';
        if(file_exists($l_file)) { include ($l_file); return; };

        // initial file
        if($module!='') {
            // module file (modules/$module/*.php)
            $l_file = MySB_ROOTPATH.'/modules/'.$module.'/includes/'.$name.'.php';
        } else {
            // file (templates/*.php)
            $l_file = MySB_ROOTPATH.'/includes/'.$name.'.php';
        }
        //echo $l_file.'<br>';
        if(file_exists($l_file)) { include ($l_file); return; }

        if( $log==true )
            $app->LOG("MySBRender::loadInclude($name,$module): include not found");
        //return false;
    }

    /**
     * Load a customisable template
     * @param   string  $name       name of the template (mytpl for templates/mytpl.php)
     * @param   string  $module     module containing the template
     * @param   string  $log        log of the *_process* par loading
     * @return  string              path to the template
     */
    public static function pathTemplate($name,$module='',$log=true) {
        global $app;
        if($module=='') $prefix = 'core';
        else $prefix = $module;
        $name = str_replace( '.', '', $name );

        // custom file (custom/*_*.php eg.:custom/core_admin/users.php)
        $l_file = MySB_ROOTPATH.'/custom/'.$prefix.'_'.$name.'.php';
        //echo $l_file.'<br>';
        if(file_exists($l_file)) { return ($l_file); };

        // initial file
        if($module!='') {   // module file (modules/$module/templates/*.php)
            $l_file = MySB_ROOTPATH.'/modules/'.$module.'/templates/'.$name.'.php';
        } else {            // file (templates/*.php)
            $l_file = MySB_ROOTPATH.'/templates/'.$name.'.php';
        }
        //echo $l_file.'<br>';
        if(file_exists($l_file)) { return ($l_file); }

        if( $log==true ) {
            $app->LOG("MySBRender::pathTemplate($name,$module): template not found");
            $app->pushAlert("Fatal: template <i>$name</i> in module <i>$module</i> not found!");
        }
        return '';
    }

    /**
     * Load a customisable include
     * @param   string  $name       name of the include (myinc for includes/myinc.php)
     * @param   string  $module     module containing the include
     * @param   string  $log        log of the *_process* par loading
     * @return  string              path to the include
     */
    public static function pathInclude($name,$module='',$log=true) {
        global $app;
        if($module=='') $prefix = 'core_includes';
        else $prefix = $module.'_includes';
        $name = str_replace( '.', '', $name );

        // custom file (custom/*_*.php eg.:custom/core_include/user_display.php)
        $l_file = MySB_ROOTPATH.'/custom/'.$prefix.'/'.$name.'.php';
        //echo $l_file.'<br>';
        if(file_exists($l_file)) { return ($l_file); };

        // initial file
        if($module!='') {
            // module file (modules/$module/*.php)
            $l_file = MySB_ROOTPATH.'/modules/'.$module.'/includes/'.$name.'.php';
        } else {
            // file (templates/*.php)
            $l_file = MySB_ROOTPATH.'/includes/'.$name.'.php';
        }
        //echo $l_file.'<br>';
        if(file_exists($l_file)) { return ($l_file); }

        if( $log==true )
            $app->LOG("MySBRender::pathInclude($name,$module): include not found");
        return '';
    }




    /**
     * Layers writing.
     */
    private function layerWrite() {
        $output = '';
        if( !$this->overlay and !$this->hidelay and !$this->itemlay )
            $output .= '
<div id="mysbMessages">
</div>
<div id="overlay" class="mysb_overlay roundtop">
    <div class="close" >
    <img src="images/window-close32.png"
         alt="'._G('SBGT_overlay_close').'"
         title="'._G('SBGT_overlay_close').'">
    </div>
    <div class="contentWrap" id="contentWrap">...</div>
</div>
<div id="hidelayer">
</div>
<script type="text/javascript">
loadSpin();
</script>';

        if( $this->overlay )
            $output .= '
<script type="text/javascript">
activeOverlay();
resizeOverWin();
$("#overlayBg").promise().done(function(){
$("#overlay").promise().done(function(){
    offSpin();
});
});
</script>';

        if( $this->itemlay )
            $output .= '
<script type="text/javascript">
show_slide(\''.$_GET['iid'].'\');
offSpin();
wrapLayerCalls();
</script>';
        else $output .= '
<script type="text/javascript">
wrapLayerCalls();
</script>';

        return $output;
    }

    /**
     * Messages and Alerts (die) writing.
     */
    public function msgWrite() {
        global $app;
        $output = '';
        if(!empty($this->Messages)) {
            $message = str_replace("\n","\\\n", $this->Messages);
            $message = str_replace("'","\'", $message);
            $output =  '
<script>
    var msgwrap = $("div#mysbMessages");
    msgwrap.promise().done(function(){
        msgwrap.html(\''.$message.'\');
        msgwrap.fadeIn(500);
        msgwrap.promise().done(function(){
            hideMessageTip();
        });
    });
</script>';
            $this->Messages = '';
        }
        if(!empty($this->Alerts)) {
            $output .= '<div id="mysbAlerts">'.$this->Alerts.'</div>';
/*
            echo '<br>
<script>
    $("div#mysbAlerts").delay(3000).fadeOut(2000);
</script>';
            echo '
            <div style="text-align: center; width: 100%;"><a href="index.php" class="button" title="'._G('SBGT_topmenu_homeinfos').'">'._G('SBGT_return_home').'</a></div><br>';
*/
/*
            $this->Alerts = '';
            $this->bodyStop();
            $app->close();
            die;
*/
        }
        return $output;
    }

    /**
     * Routing for controler call
     */
    public function ctrl_route() {
        
        if( !empty($_GET['tpl']) ) {
            /*
            ob_start();
            if ( $content = $this->loadTemplate($_GET['tpl'].'_ctrl',$_GET['mod'],true)) {
                $content = ob_get_clean();
                echo $this->view_render($this->layerWrite().$content);
                return true;
            }
            */
            if ( ($file = $this->pathTemplate($_GET['tpl'].'_ctrl',$_GET['mod'],true))!='') {
                ob_start();
                include($file);
                $content = ob_get_clean();
                echo $this->view_render($this->layerWrite().$this->msgWrite().$content);
                return true;
            } else
                return false;

        } elseif( !empty($_GET['inc']) ) {
            ob_start();
            //_incI($_GET['inc'].'_ctrl',$_GET['mod'],false);
            $this->loadInclude($_GET['inc'].'_ctrl',$_GET['mod'],true);
            $content = ob_get_clean();
            echo $content.$this->msgWrite().$this->layerWrite();
            //echo 'TEST'.$content;
            return true;

        } else {
            $pluginsFrontPage = MySBPluginHelper::loadByType('FrontPage');
            $content = '';
            foreach($pluginsFrontPage as $plugin) {
                $content .= $plugin->callControler();
            }
            $this->view_render($content.$this->msgWrite().$this->layerWrite());
            return true;
        }
        //throw new Exception("Action non valide");
        return false;
        //$this->display->header();
    }

    /**
     * register local custom header line
     * @param   string     $header      Header line to append in <head section> (null to reset)
     */
    public function headerADD($header=null) {
        if( $header==null )
            $this->custom_headers = array();
        else
            $this->custom_headers[] = $header;
    }

    /**
     * Rendering view
     */
    public function view_refresh($refresh_time) {
        //global $app;
        $this->refresh_time = $refresh_time;
    }

    /**
     * Rendering view
     */
    public function view_menu($show_topmenu) {
        //global $app;
        $this->show_topmenu = $show_topmenu;
    }

    /**
     * Rendering view
     */
    public function view_render($content) {
        global $app;
        if($this->overlay==1) {
            echo $content;
            return;
        }
        $this->content['template'] = $content;
        ob_start();
        include( $this->pathTemplate('template','',true));
        //require (MySB_ROOTPATH.'/templates/template.php');
        echo ob_get_clean();
    }

    /**
     * Rendering view
     */
    public function includeView($file,$module) {
        ob_start();
        $this->loadTemplate($file,$module);
        //$content = ob_get_clean();
        /*
        if( $lastview ) {
            $this->content['template'] = $content;
            ob_start();
            $this->loadTemplate('template','',true);
            //require (MySB_ROOTPATH.'/templates/template.php');
            $content = ob_get_clean();
        }
        */
        //return $content;
        //} else 
        //    echo $this->content['middle'];
        return ob_get_clean();
    }
}

/**
 * Include a localised template (sequential form)
 * @param   string  $file         file translated
 * @param   string  $module         module containing the localized string
 * @return  boolean
 */
function _incV($file,$module='') { global $app; return $app->includeView($file,$module); }

/**
 * Load a customisable template (sequential form of MySBUtil::loadTemplate())
 * @param   string  $name       name of the template (mytpl for templates/mytpl.php)
 * @param   string  $module     module containing the template
 * @param   string  $log        log of the *_process* par loading
*/
function _pathT($name,$module='',$log=true) { global $app; return $app->pathTemplate($name,$module,$log); }
/**
 * Load a customisable include (sequential form of MySBUtil::loadInclude())
 * @param   string  $name       name of the include (mytpl for includes/myinc.php)
 * @param   string  $module     module containing the include
 * @param   string  $log        log of the *_process* par loading
*/
function _pathI($name,$module='',$log=true) { global $app; return $app->pathInclude($name,$module,$log); }
/**
 * Load a customisable template (sequential form of MySBUtil::loadTemplate())
 * @param   string  $name       name of the template (mytpl for templates/mytpl.php)
 * @param   string  $module     module containing the template
 * @param   string  $log        log of the *_process* par loading
*/
function _incT($name,$module='',$log=true) { global $app; return $app->loadTemplate($name,$module,$log); }
/**
 * Load a customisable include (sequential form of MySBUtil::loadInclude())
 * @param   string  $name       name of the include (mytpl for includes/myinc.php)
 * @param   string  $module     module containing the include
 * @param   string  $log        log of the *_process* par loading
*/
function _incI($name,$module='',$log=true) { global $app; return $app->loadInclude($name,$module,$log); }

?>
