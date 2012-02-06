<?php

if(!defined('DOKU_INC')) die();

class action_plugin_publish_revisions extends DokuWiki_Action_Plugin {

    private $hlp;

    function __construct() {
        $this->hlp = plugin_load('helper','publish');
    }

    function register(&$controller) {
        $controller->register_hook('HTML_REVISIONSFORM_OUTPUT', 'BEFORE', $this, 'handle_revisions', array());
    }

    function handle_revisions(&$event, $param) {
        global $ID;

        if (!$this->hlp->in_namespace($this->getConf('apr_namespaces'), $ID)) {
            return;
        }

        $meta = p_get_metadata($ID);
        $latest_rev = $meta['last_change']['date'];

        $member = null;
        foreach ($event->data->_content as $key => $ref) {
            if($ref['_elem'] == 'opentag' && $ref['_tag'] == 'div' && $ref['class'] == 'li') {
                $member = $key;
            }

            if ($member && $ref['_elem'] == 'tag' &&
                $ref['_tag'] == 'input' && $ref['name'] == 'rev2[]'){
                if ($meta['approval'][$ref['value']] ||
                    ($ref['value'] == 'current' && $meta['approval'][$latest_rev])) {
                    $event->data->_content[$member]['class'] = 'li approved_revision';
                } else {
                    $event->data->_content[$member]['class'] = 'li unapproved_revision';
                }
                $member = null;
            }
        }

        return true;
    }

}