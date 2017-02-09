@start('header')

    {{ $app->assets(['advancedforms:assets/forms.js','advancedforms:assets/js/form.js'], $app['cockpit/version']) }}

@end('header')

<div data-ng-controller="form" data-id="{{ $id }}" ng-cloak>

    <h1>
        <a href="@route("/advancedforms")">@lang('Advanced Forms')</a> /
        <span class="uk-text-muted" ng-show="!form.name">@lang('Form')</span>
        <span ng-show="form.name">@@ form.name @@</span>
    </h1>


    <form class="uk-form" data-ng-submit="save()" data-ng-show="form">

        <div class="uk-grid" data-uk-grid-margin>

            <div class="uk-width-medium-1-2">

                <div class="app-panel">

                    <div class="uk-form-row">
                        <input class="uk-width-1-1 uk-form-large" type="text" placeholder="@lang('Name')" data-ng-model="form.name" required>
                    </div>

                    <div class="uk-form-row">
                        <label class="uk-text-small">Email</label>
                        <input class="uk-width-1-1 uk-form-large" type="text" placeholder="@lang('Email form data to this address')" data-ng-model="form.email">

                        <div class="uk-alert">
                            @lang('Leave the email field empty if you don\'t want to recieve any form data via email.')
                        </div>
                    </div>

                    <div class="uk-form-row">
                        <label class="uk-text-small">Email Subject</label>
                        <input class="uk-width-1-1 uk-form-large" type="text" placeholder="@lang('Subject for the email')" data-ng-model="form.emailSubject">
                    </div>

                    <div class="uk-form-row">
                        <label class="uk-text-small">@lang('Max Upload File Size in MB')</label>
                        <input class="uk-width-1-1 uk-form-large" type="text" placeholder="@lang('example: 15')" data-ng-model="form.maxFileSize" ">
                    </div>

                    <div class="uk-form-row">
                        <label class="uk-text-small">@lang('File Size Exceeded Message')</label>
                        <input class="uk-width-1-1 uk-form-large" type="text" placeholder="@lang('Message to be displayed if file is too big')" data-ng-model="form.maxFileSizeMessage" ">
                    </div>

                     <div class="uk-form-row">
                        <label class="uk-text-small">@lang('Allowed File Upload Extensions')</label>
                        <input class="uk-width-1-1 uk-form-large" type="text" placeholder="@lang('example: pdf,jpg,gif')" data-ng-model="form.extensions" data-ng-change="extensionsChanged()">

                        <div class="uk-alert">
                            @lang('Separate extensions with comas')
                        </div>
                    </div>


                    <div class="uk-form-row">
                        <input type="checkbox" data-ng-model="form.useCaptcha"> @lang('Use noCaptcha')  

                        <div class="uk-alert">
                            @lang('To activate noCaptcha you must set the following two entries in the Cockpit registry: <strong>AdvancedFormsNoCaptcha_SecretKey</strong> and <strong>AdvancedFormsNoCaptcha_SiteKey</strong>. Get the keys from ') <a href="https://www.google.com/recaptcha/admin" target="_blank">Google reCaptcha</a>.
                        </div>                      
                    </div>   

                    <div class="uk-form-row">
                        <label class="uk-text-small">@lang('noCaptcha language code')</label>
                        <input class="uk-width-1-1 uk-form-large" type="text" placeholder="@lang('enter code or blank for autodetect')" data-ng-model="form.noCaptchaLanguage">

                        <div class="uk-alert">
                            @lang('blank=autodetect from client, en= force English, bg= force Bulgarian, etc.') <a href="https://developers.google.com/recaptcha/docs/language" target="_blank">@lang('See all language codes')</a>.
                        </div>
                    </div>

                    <div class="uk-form-row">
                        <label class="uk-text-small">@lang('noCaptcha required prompt message')</label>
                        <input class="uk-width-1-1 uk-form-large" type="text" placeholder="@lang('blank = generic message in English')" data-ng-model="form.noCaptchaRequired">

                        <div class="uk-alert">
                            @lang('The message displayed to the user it they try to submit the form without checking the captcha checkbox.')
                        </div>
                    </div>

                    
                    <div class="uk-form-row">
                        <input type="checkbox" data-ng-model="form.entry"> @lang('Save form data')
                    </div>
                    
                    <div class="uk-form-row">

                        <div class="uk-button-group">
                            <button type="submit" class="uk-button uk-button-primary uk-button-large">@lang('Save form')</button>
                            <a href="@route('/advancedforms/entries')/@@ form._id @@" class="uk-button uk-button-large" data-ng-show="form._id"><i class="uk-icon-list"></i> @lang('Goto entries')</a>
                        </div>
                        &nbsp;
                        <a href="@route('/advancedforms')">@lang('Cancel')</a>
                    </div>

                </div>
            </div>

            <div class="uk-width-medium-1-2">

                <div class="uk-margin" ng-show="form.name">
                    <strong>@lang('Form snippet example'):</strong>

<highlightcode>&lt;?php 
        advancedform('@@form.name@@', [
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
?&gt;
    &lt;p&gt;
        &lt;label&gt;Name&lt;/label&gt;
        &lt;input type="text" name="<i>form</i>[Your Name]" required&gt;
    &lt;/p&gt;
    &lt;p&gt;
        &lt;label&gt;Name&lt;/label&gt;
        &lt;input type="email" name="<i>form</i>[Email Address]" required&gt;
    &lt;/p&gt;
    &lt;p&gt;
        &lt;label&gt;Message&lt;/label&gt;
        &lt;textarea name="<i>form</i>[message]" required&gt;&lt;/textarea&gt;
    &lt;/p&gt;
    &lt;p&gt;
        &lt;label&gt;Resume&lt;/label&gt;
        &lt;input type="file" name="<i>formfiles</i>[resume]" required&gt;
    &lt;/p&gt;
    &lt;p&gt;
        &lt;label&gt;Photo&lt;/label&gt;
        &lt;input type="file" name="<i>formfiles</i>[photo]" required&gt;
    &lt;/p&gt;
    &lt;div id="captcha-placeholder"&gt;&lt;/div&gt;
    &lt;p&gt;
        &lt;button type="submit"&gt;Send&lt;/button&gt;
    &lt;/p&gt;
&lt;/form&gt;


&lt;div class="form-success-message" style="display:none"&gt;
    Got it. Successfully got your message!
&lt;/div&gt;
&lt;div class="form-fail-message" style="display:none"&gt;
    Oops. Something happened!
&lt;/div&gt;

&lt;script&gt;
    var onFormSubmitSuccess = function() {alert("Got it!");};
    var onFormSubmitFail = function() {alert("Oops :(");};
    var onFormSubmitStarted = function() {$(".spinner").show();};
    var onFormSubmitFinished = function() {$(".spinner").hide();};
&lt;/script&gt;
</highlightcode>

<div class="uk-alert uk-alert-info">
    <i class="uk-icon-exclamation-circle"></i>
    @lang('It is important to prefix the form fields with <strong>form[...]</strong> and the file uploads with <strong>formfiles[...]</strong>. The values in [...] will be used as labels to display the submitted data or as names of the  files.')
</div>

                </div>
            </div>
        </div>

    </form>
</div>
