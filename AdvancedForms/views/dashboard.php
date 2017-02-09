@if(count($forms))

    <div class="uk-margin-bottom">
        <span class="uk-button-group">
            @hasaccess?("AdvancedForms", 'manage.forms')
            <a class="uk-button uk-button-success uk-button-small" href="@route('/advancedforms/form')" title="@lang('Add form')" data-uk-tooltip="{pos:'bottom'}"><i class="uk-icon-plus-circle"></i></a>
            @end
            <a class="uk-button app-button-secondary uk-button-small" href="@route('/advancedforms')" title="@lang('Show all forms')" data-uk-tooltip="{pos:'bottom'}"><i class="uk-icon-ellipsis-h"></i></a>
        </span>
    </div>

    <span class="uk-text-small uk-text-uppercase uk-text-muted">@lang('Latest')</span>
    <ul class="uk-list uk-list-space">
        @foreach($forms as $form)
        <li><a href="@route('/advancedforms/entries/'.$form['_id'])"><i class="uk-icon-map-marker"></i> {{ $form["name"] }}</a></li>
        @endforeach
    </ul>

@else

    <div class="uk-text-center">
        <h2><i class="uk-icon-file-text"></i></h2>
        <p class="uk-text-muted">
            @lang('You don\'t have any forms created.')
        </p>

        @hasaccess?("AdvancedForms", 'manage.forms')
        <a href="@route('/advancedforms/form')" class="uk-button uk-button-success" title="@lang('Create a form')" data-uk-tooltip="{pos:'bottom'}"><i class="uk-icon-plus-circle"></i></a>
        @end
    </div>

@endif
