# cockpit-module-advancedforms
This module was built on top of the core Forms module of [Cockpit CMS](http://https://github.com/COCOPi/cockpit/tree/master) (standard version as of January 2017, not Cockpit Next).

The module has been tested with one client only, so please be aware of any potential bugs.

![screencapture-localhost-bw-dentalx_cockpit-cockpit-advancedforms-form-589935721c3e2doc1183320716-1486665939943](https://cloud.githubusercontent.com/assets/6288683/22797958/d7934972-eec5-11e6-877a-611623beeccf.png)

## Installation
Just download the AdvancedForms folder and put it in your <code>\<root\>/\<cockpit\>/modules/addons</code> folder. 

## Features

This is a list of all features that were added on top of the core Forms module:

1. **File upload:** 
  * You can place multiple <code>\<input type="file" name="formfiles[Form Field Name]"\></code> fields on your form. Uploaded files are being stored in the <code>\<root\>/\<cockpit\>/storage/advancedforms</code> folder. If not present, it will be created upon the first form submission.

  * An .htaccess will be written into that folder preventing direct access to uploaded files from the web if hosted on Apache. For other servers make sure you secure this folder.

  * The uploaded files can be downloaded via the form entries view in Cockpit admin.

  * If an email is provided, all submitted files will be emailed as attachments.
  
1. **reCaptcha 2 (noCaptcha)**
  * If enabled from the form settings a capcha field will be placed in an element of your choice inside your form. This is passed as a form option when you initialize your form (see code example below).
  * To activate noCaptcha you must set the following two entries in the Cockpit registry: AdvancedFormsNoCaptcha_SecretKey and AdvancedFormsNoCaptcha_SiteKey. Get the keys from [Google reCaptcha](https://www.google.com/recaptcha/admin).

1. **Form Rendering Options** The following can be passed as options to <code>advancedform</code>:
  * **captchaId** - *required if using reCaptcha* - the html elemend id where to render the noCaptcha
  * **fromNameInputName** - *optional* - the input to use as the "From" name when sending the email
  * **fromEmailInputName** - *optional* - the input to use as the "From" email when sending the email - be aware than some email serviced such as gmail may block emails sent as someone else.
  * **successElementClassName** - *optional* upon successful submission show this element
  * **failElementClassName** - *optional* upon failed submission show this element
  * **successCallback** - *optional* - call this JS function upon successful submission
  * **failCallback** - *optional* - call this JS function upon failed submission
  * **submitStartedCallback** - *optional* - called when AJAX submission starts
  * **submitFinishedCallback** - *optional* - called when AJAX submission ends regardless of the outcome
  
1. **Sample form code with all options**
  
```php
<?php 
        advancedform('JobApplication', [
            'captchaId'=>'captcha-placeholder',
            'fromEmailInputName' => 'form[Your Name]', 
            'fromNameInputName' => 'form[Email Address]',
            'successElementClassName' => 'form-success-message',
            'failElementClassName' => 'form-fail-message',
            'successCallback' => 'onFormSubmitSuccess',
            'failCallback' => 'onFormSubmitFail',
            'submitStartedCallback' => 'onFormSubmitStarted',
            'submitFinishedCallback' => 'onFormSubmitFinished'
        ]); 
?>
    <p>
        <label>Name</label>
        <input type="text" name="form[Your Name]" required>
    </p>
    <p>
        <label>Name</label>
        <input type="email" name="form[Email Address]" required>
    </p>
    <p>
        <label>Message</label>
        <textarea name="form[message]" required></textarea>
    </p>
    <p>
        <label>Resume</label>
        <input type="file" name="formfiles[resume]" required>
    </p>
    <p>
        <label>Photo</label>
        <input type="file" name="formfiles[photo]" required>
    </p>
    <div id="captcha-placeholder"></div>
    <p>
        <button type="submit">Send</button>
    </p>
</form>

<div class="form-success-message" style="display:none">
    Got it. Successfully got your message!
</div>
<div class="form-fail-message" style="display:none">
    Oops. Something happened!
</div>

<script>
    var onFormSubmitSuccess = function() {alert("Got it!");};
    var onFormSubmitFail = function() {alert("Oops :(");};
    var onFormSubmitStarted = function() {$(".spinner").show();};
    var onFormSubmitFinished = function() {$(".spinner").hide();};
</script>
```
