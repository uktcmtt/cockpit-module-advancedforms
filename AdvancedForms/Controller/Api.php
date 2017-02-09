<?php

namespace AdvancedForms\Controller;

class Api extends \Cockpit\Controller {


    public function find(){

        $options = [];

        if ($filter = $this->param("filter", null)) $options["filter"] = $filter;
        if ($limit  = $this->param("limit", null))  $options["limit"] = $limit;
        if ($sort   = $this->param("sort", null))   $options["sort"] = $sort;
        if ($skip   = $this->param("skip", null))   $options["skip"] = $skip;

        $docs = $this->app->db->find("advancedforms/forms", $options);

        if (count($docs) && $this->param("extended", false)){
            foreach ($docs as &$doc) {
                $doc["count"] = $this->app->module("advancedforms")->collectionById($doc["_id"])->count();
            }
        }

        return json_encode($docs->toArray());
    }

    public function findOne(){

        $doc = $this->app->db->findOne("advancedforms/forms", $this->param("filter", []));

        return $doc ? json_encode($doc) : '{}';
    }


    public function save(){

        $form = $this->param("form", null);

        if ($form) {

            $form["modified"] = time();
            $form["_uid"]     = @$this->user["_id"];

            if (!isset($form["_id"])){
                $form["created"] = $form["modified"];
            }

            $this->app->db->save("advancedforms/forms", $form);
        }

        return $form ? json_encode($form) : '{}';
    }

    public function remove(){

        $form = $this->param("form", null);

        if ($form) {
            $frm = "form".$form["_id"];

            $this->app->db->dropCollection("advancedforms/{$frm}");
            $this->app->db->remove("advancedforms/forms", ["_id" => $form["_id"]]);

            //remove the uploaded files
            $target = $this->path("#root:storage/advancedforms/") . $form["_uid"];
  
            $files = glob( $target . '/*', GLOB_MARK ); 

            foreach( $files as $file )
            {
                unlink( $file );      
            }
          
            rmdir( $target );
            
        }

        return $form ? '{"success":true}' : '{"success":false}';
    }


    public function entries() {

        $form = $this->param("form", null);
        $entries    = [];

        if ($form) {

            $frm     = "form".$form["_id"];
            $options = [];

            if ($filter = $this->param("filter", null)) $options["filter"] = $filter;
            if ($limit  = $this->param("limit", null))  $options["limit"] = $limit;
            if ($sort   = $this->param("sort", null))   $options["sort"] = $sort;
            if ($skip   = $this->param("skip", null))   $options["skip"] = $skip;

            $entries = $this->app->db->find("advancedforms/{$frm}", $options);
        }

        return json_encode($entries->toArray());
    }

    public function removeentry(){

        $form = $this->param("form", null);
        $entryId    = $this->param("entryId", null);

        if ($form && $entryId) {

            $frm = "form".$form["_id"];

            $entry = $this->app->db->find("advancedforms/{$frm}", ["_id" => $entryId]);
           
            $fields = $entry->toArray()[0]["data"];


            foreach($fields as $field) {               
                if (strpos($field, '#root:') === 0) {
                   unlink($this->path("#root:") . substr($field, 6));
                }
            }
            

            $this->app->db->remove("advancedforms/{$frm}", ["_id" => $entryId]);
        }

        return ($form && $entryId) ? '{"success":true}' : '{"success":false}';
    }

    public function emptytable(){

        $form = $this->param("form", null);

        if ($form) {

            $form = "form".$form["_id"];

            $this->app->db->remove("advancedforms/{$form}", []);

            //remove the uploaded files
            $target = $this->path("#root:storage/advancedforms/") . $form["_uid"];
            
            $files = glob( $target . '/*', GLOB_MARK ); 

            foreach( $files as $file )
            {
                unlink( $file );      
            }
            
            rmdir( $target );
        }

        return $form ? '{"success":true}' : '{"success":false}';
    }

    public function saveentry(){

        $form = $this->param("form", null);
        $entry      = $this->param("entry", null);

        if ($form && $entry) {

            $frm = "form".$form["_id"];

            $entry["modified"] = time();
            $entry["_uid"]     = @$this->user["_id"];

            if (!isset($entry["_id"])){
                $entry["created"] = $entry["modified"];
            }

            $this->app->db->save("advancedforms/{$frm}", $entry);
        }

        return $entry ? json_encode($entry) : '{}';
    }

    public function export($formId) {

        if (!$this->app->module("auth")->hasaccess("AdvancedForms", 'manage.forms')) {
            return false;
        }

        $form = $this->app->db->findOneById("advancedforms/forms", $formId);

        if (!$form) return false;

        $col     = "form".$form["_id"];
        $entries = $this->app->db->find("advancedforms/{$col}");

        return json_encode($entries, JSON_PRETTY_PRINT);
    }

}