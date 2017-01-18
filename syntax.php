<?php
/**
 * Plugin googlecal2: Inserts an Google Calendar iframe
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Niklas Fejes (@nfejes)
 *
 *
 * Plugin googlecal: Inserts an Google Calendar iframe
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Kite <Kite@puzzlers.org>,  Christopher Smith <chris@jalakai.co.uk>
 * @seealso    (http://www.dokuwiki.org/plugin:iframe)
 */

if(!defined('DOKU_INC')) define('DOKU_INC',realpath(dirname(__FILE__).'/../../').'/');
if(!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN',DOKU_INC.'lib/plugins/');
require_once(DOKU_PLUGIN.'syntax.php');

/**
 * All DokuWiki plugins to extend the parser/rendering mechanism
 * need to inherit from this class
 */
class syntax_plugin_googlecal extends DokuWiki_Syntax_Plugin {

    function getType() { return 'substition'; }
    
    function getPType(){ return 'block'; }
    
    function getSort() { return 319; }
    
    function connectTo($mode) {
        $this->Lexer->addSpecialPattern('{{cal2>[^}]*?}}', $mode, 'plugin_googlecal2');
    }

    function handle($match, $state, $pos, Doku_Handler $handler){        
        if(preg_match('/{{cal2>(.*)/', $match)) {             // Hook for future features
            // Handle the simplified style of calendar tag
            $match = html_entity_decode(substr($match, 6, -2));
            @list($url, $alt) = explode('|',$match,2);
            $matches = array();
            
            // '/^\s*([^\[|]+)(?:\[(?:([^,\]]*),)?([^,\]]*)\])?(?:\s*(?:\|\s*(.*))?)?$/mD'
            if (preg_match('/(.*)\[(.*)\]$/', trim($url), $matches)) {
                $url = $matches[1];
                if (strpos($matches[2],',') !== false) {
                    @list($w, $h) = explode(',',$matches[2],2);
                } else {
                    $h = $matches[2];
                    $w = '100%';
                }
            } else {
                $w = '100%';
                $h = '600';
            }
            if (!isset($alt)) $alt = '';

            $css = "border: solid 2px black;box-sizing: border-box;";
            if (substr($w,-1) === "%") {
                $css .= "width: $w;";
                $w = '800';
            }
            
            if (!$this->getConf('js_ok') && substr($url,0,11) == 'javascript:') {
                return array('error', $this->getLang('gcal_No_JS'));
            }
            return array('wiki', hsc(trim("$url")), hsc(trim($alt)), hsc(trim($w)), hsc(trim($h)), hsc(trim($css)));
        } else {
            return array('error', $this->getLang("gcal_Bad_iFrame"));  // this is an error
        } // matched {{cal>...
    }

    function render($mode, Doku_Renderer $renderer, $data) {
        list($style, $url, $alt, $w, $h, $css) = $data;
        
        if($mode == 'xhtml'){
            // Two styles: wiki and error
            switch($style) {
                case 'wiki':
                    $src = "https://calendar.google.com/calendar/embed?src=$url";
                    $renderer->doc .= "<iframe src='$src' title='$alt' width='$w' height='$h' " . 
                                      "frameborder='0' scrolling='no' style='$css'></iframe>\n";
                    break;
                case 'error':
                    $renderer->doc .= "<div class='error'>$url</div>";
                    break;
                default:
                    $renderer->doc .= "<div class='error'>" . $this->getLang('gcal_Invalid_mode') . "</div>";
                    break;
            }
            return true;
        }
        return false;
    }
}
