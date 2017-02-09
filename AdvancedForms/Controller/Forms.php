<?php

namespace AdvancedForms\Controller;

class Forms extends \Cockpit\Controller {

	public function index(){
        return $this->render("advancedforms:views/index.php");
    }

    public function form($id = null) {

        if (!$this->app->module("auth")->hasaccess("AdvancedForms", 'manage.forms')) {
            return false;
        }

        return $this->render("advancedforms:views/form.php", compact('id'));
    }

    public function entries($id) {

        $form = $this->app->db->findOne("advancedforms/forms", ["_id" => $id]);

        if (!$form) {
            return false;
        }

        $count = $this->app->module("advancedforms")->collectionById($form["_id"])->count();

        $form["count"] = $count;

        return $this->render("advancedforms:views/entries.php", compact('id', 'form', 'count'));
    }

    public function file() {
        $filename = explode("/", $_GET["f"]);
        $filename = $filename[count($filename)-1];
        
        header("Content-Type: application/octet-stream");
        header("Content-Transfer-Encoding: Binary");
        header("Content-disposition: attachment; filename=\"" . $filename . "\""); 
        echo readfile($this->path($_GET["f"]));
    }
}