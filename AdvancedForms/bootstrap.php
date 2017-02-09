<?php

// API

$app->bind("/api/advancedforms/submit/:form", function($params) use($app) {

    $form = $params["form"];

    // Security check

    if ($formhash = $this->param("__csrf", false)) {

        if ($formhash != $this->hash($form)) {
            return false;
        } 

    } else {
        return false;
    }

    $frm = $this->db->findOne("advancedforms/forms", ["name"=>$form]);

    if (!$frm) {
        return false;
    }

    if ($formdata = $this->param("form", false)) {

        // custom form validation
        if ($this->path("custom:advancedforms/{$form}.php") && false===include($this->path("custom:advancedforms/{$form}.php"))) {
            return false;
        }

        if (isset($frm["useCaptcha"]) && $frm["useCaptcha"]) {
            $captchaSecretKey = get_registry('AdvancedFormsNoCaptcha_SecretKey', false);
            if ($captchaSecretKey) {
                $captchaResponse = $this->param("g-recaptcha-response", false);
                if ($captchaResponse) {
                    $verify=file_get_contents("https://www.google.com/recaptcha/api/siteverify?secret=" . $captchaSecretKey . "&response=" . $captchaResponse );

                    $captchaSuccess=json_decode($verify);

                    if ($captchaSuccess->success==false) {
                        return false;
                    }
                } else {
                    return false;
                }
            }
        }

        //make main directory to upload advanced forms            
        $thepath = $this->path("#root:storage/advancedforms/");
        if ($thepath == null) {
           mkdir($this->path("#root:storage/") . 'advancedforms'); 
           $thepath = $this->path("#root:storage/advancedforms/");   

           //create .htaccess to prevent direct file download
           file_put_contents($thepath . '.htacces', "Deny from all\n");
        }

        $fileAttachments = [];

        if (isset($_FILES) && isset($_FILES["formfiles"]) && isset($_FILES["formfiles"]["name"])) {
        
            //make form directory to upload advanced forms            
            $relpath =  "#root:storage/advancedforms/" . $frm["_uid"] . "/";
            $thepath = $this->path($relpath);
            if ($thepath == null) {
                mkdir($this->path("#root:storage/advancedforms/") . $frm["_uid"]); 
                $thepath = $this->path($relpath);                  
            }

            if (isset($frm["extensions"])) {
               $allowedExtensions = explode(",", $frm["extensions"]);
            }

            foreach ($_FILES["formfiles"]["name"] as $key=>$filename) {
              
               if ($filename != '')
               {
                   //check against allowed extensions for this form
                   $extension  = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
                   if (!in_array($extension, $allowedExtensions)) {
                       continue;
                   }

                   $time   = time();
                   $randomLength = 5;
                   $random =  substr(str_shuffle(str_repeat($x='0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ', ceil($randomLength/strlen($x)) )),1,$randomLength);
                   $newFilename   = $key . '-' . $random . '-' . $time;                                      
                   $newFilename = $newFilename . '.' . $extension;
                   $attachedFileName = $newFilename; 
                   $newFilename = $thepath . $newFilename;
                   move_uploaded_file($_FILES["formfiles"]['tmp_name'][$key], $newFilename);

                   array_push($fileAttachments, array(
                        "attachment" => $newFilename,
                        "name" => $attachedFileName,
                        "relName" => $relpath . $attachedFileName,
                        "key" => $key
                    ));
               }
            }
        }
        

        if (isset($frm["email"])) {

            $emails          = array_map('trim', explode(',', $frm['email']));
            $filtered_emails = [];

            foreach($emails as $to) {
                // Validate each email address individually, push if valid
                if (filter_var($to, FILTER_VALIDATE_EMAIL)) {
                    $filtered_emails[] = $to;
                }
            }

            if (count($filtered_emails)) {

                $frm['email'] = implode(',', $filtered_emails);

                // There is an email template available
                if ($template = $this->path("custom:advancedforms/emails/{$form}.php")) {
                    $body = $this->renderer->file($template, $formdata, false);
                // Prepare template manually
                } else {
                    $body = [];

                    foreach ($formdata as $key => $value) {
                        $body[] = "<b>{$key}:</b><br>";
                        $body[] = (is_string($value) ? $value:json_encode($value))."\n<br><br>";
                    }

                    $body = implode("", $body);
                }

                
                
                $formSubject = (isset($frm['emailSubject']) && $frm['emailSubject'] != "") ? $frm['emailSubject'] : "New form data for: " . $form;

                //from email
                $fromEmail = "";
                if ($this->param("__fromEmailInputName") !== null) {
                    $fieldName = [];
                    preg_match('/\[(.*?)\]/', $this->param("__fromEmailInputName"), $fieldName);
                    if (isset($formdata[$fieldName[1]])) {
                        $fromEmail = $formdata[$fieldName[1]];
                    }
                }
                $fromEmail = ($fromEmail != "") ? $fromEmail : 'mailer@'.(isset($_SERVER["SERVER_NAME"]) ? $_SERVER["SERVER_NAME"] : 'localhost');

                 //from name
                $fromName = "";
                if ($this->param("__fromNameInputName") !== null) {
                    $fieldName = [];
                    preg_match('/\[(.*?)\]/', $this->param("__fromNameInputName"), $fieldName);
                    if (isset($formdata[$fieldName[1]])) {
                        $fromName = $formdata[$fieldName[1]];
                    }
                }

                $fromName = ($fromName != "") ? $fromName : false;                

                $message = $this->mailer->createMessage($frm["email"], $formSubject, $body);
                $message->setFrom($fromEmail, $fromName);
            

                foreach ($fileAttachments as $attachment) {                    
                   $message->attach($attachment["attachment"], $attachment["name"]);
                }


                $message->send();
            }
        }


        


        if (isset($frm["entry"]) && $frm["entry"]) {
            //insert the files into formdata
            foreach ($fileAttachments as $file) {
                $formdata[$file["key"]] = $file["relName"];                
            }


            $collection = "form".$frm["_id"];
            $entry      = ["data" => $formdata, "created"=>time()];
            $this->db->insert("advancedforms/{$collection}", $entry);
        }


        return json_encode($formdata);        

    } else {
        return "false";
    }

});


$this->module("advancedforms")->extend([

    "get_form" => function($name) use($app) {

        static $forms;

        if (null === $forms) {
            $forms = [];
        }

        if (!isset($forms[$name])) {
            $forms[$name] = $app->db->findOne("advancedforms/forms", ["name"=>$name]);
        }

        return $forms[$name];
    },

    "advancedform" => function($name, $options = []) use($app) {

        $form = $this->get_form($name);
        $allowedExtensions = (isset($form["extensions"])) ? $form["extensions"] : "";
        $allowedExtensions = str_replace(",",", .",$allowedExtensions);
        if (strlen($allowedExtensions) > 0) {
            $allowedExtensions = "." . $allowedExtensions;   
        }

        $captchaSiteKey = get_registry('AdvancedFormsNoCaptcha_SiteKey', false);
        $captchaSecretKey = get_registry('AdvancedFormsNoCaptcha_SecretKey', false);
        $useCaptcha = ($captchaSiteKey && $form["useCaptcha"]);
        $useCaptcha = ($captchaSiteKey && $captchaSecretKey && $form["useCaptcha"]); 


        $options = array_merge(array(
            "id"    => uniqid("form"),
            "class" => "",
            "csrf"  => $app->hash($name),
            "allowedExtensions" => $allowedExtensions,
            "useCaptcha" => $useCaptcha,
            "noCaptchaLanguage" => $form["noCaptchaLanguage"],
            "noCaptchaRequired" => $form["noCaptchaRequired"],
            "maxFileSize" => (isset($form["maxFileSize"])) ? $form["maxFileSize"] : 15,
            "maxFileSizeMessage" => (isset($form["maxFileSizeMessage"])) ? $form["maxFileSizeMessage"]  : false,
            "captchaSiteKey" => $captchaSiteKey
        ), $options);


        $app->renderView("advancedforms:views/api/form.php", compact('name', 'options'));        
    },

    "collectionById" => function($formId) use($app) {

        $entrydb = "form{$formId}";

        return $app->db->getCollection("advancedforms/{$entrydb}");
    },

    "entries" => function($name) use($app) {

        $form = $this->get_form($name);

        if (!$form) {
            return false;
        }

        $entrydb = "form".$form["_id"];

        return $app->db->getCollection("advancedforms/{$entrydb}");
    }
]);


if (!function_exists('advancedform')) {

    function advancedform($name, $options = []) {
        cockpit("advancedforms")->advancedform($name, $options);
    }
}



// ADMIN
if (COCKPIT_ADMIN && !COCKPIT_REST) include_once(__DIR__.'/admin.php');
