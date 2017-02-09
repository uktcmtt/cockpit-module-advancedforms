<?php

// ACL
$app("acl")->addResource("AdvancedForms", ['manage.forms', 'manage.entries']);

$app->on("admin.init", function() {

    if (!$this->module("auth")->hasaccess("AdvancedForms", ['manage.forms', 'manage.entries'])) return;

    $this->bindClass("AdvancedForms\\Controller\\Forms", "advancedforms");
    $this->bindClass("AdvancedForms\\Controller\\Api", "api/advancedforms");

    $this("admin")->menu("top", [
        "url"    => $this->routeUrl("/advancedforms"),
        "label"  => '<i class="uk-icon-file-text"></i>',
        "title"  => $this("i18n")->get("Advanced Forms"),
        "active" => (strpos($this["route"], '/advancedforms') === 0)
    ], 5);

    // handle global search request
    $this->on("cockpit.globalsearch", function($search, $list) {

        foreach ($this->db->find("advancedforms/forms") as $f) {
            if (stripos($f["name"], $search)!==false){
                $list[] = [
                    "title" => '<i class="uk-icon-file-text"></i> '.$f["name"],
                    "url"   => $this->routeUrl('/advancedforms/form/'.$f["_id"])
                ];
            }
        }
    });
});

$app->on("admin.dashboard.aside", function() {

    if (!$this->module("auth")->hasaccess("AdvancedForms", ['manage.forms', 'manage.entries'])) return;

    $title = $this("i18n")->get("Advanced Forms");
    $badge = $this->db->getCollection("advancedforms/forms")->count();
    $forms = $this->db->find("advancedforms/forms", ["limit"=> 3, "sort"=>["created"=>-1] ])->toArray();

    $this->renderView("advancedforms:views/dashboard.php with cockpit:views/layouts/dashboard.widget.php", compact('title', 'badge', 'forms'));
});
