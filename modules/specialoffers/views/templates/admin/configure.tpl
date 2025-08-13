<!-- tabs -->
<ul class="nav nav-tabs" role="tablist">
    <li class="{if $active_tab == 'settings'}active{/if}">
        <a href="#tab-settings" data-toggle="tab">{l s='Settings' mod='specialoffers'}</a>
    </li>
    <li class="{if $active_tab == 'style'}active{/if}">
        <a href="#tab-style" data-toggle="tab">{l s='Style' mod='specialoffers'}</a>
    </li>
</ul>


<div class="tab-content" >

    <!-- banner list and edit/add form -->
    <div class="tab-pane {if $active_tab == 'settings'}active{/if}" id="tab-settings">

        <!-- edit/add form -->
        {if $show_form}
            {$form_settings nofilter}
        {else}
            <!-- banner list -->
            {$list_banners nofilter}
            {$form_module_enable nofilter}
        {/if}

    </div>


    <!-- style form -->
    <div class="tab-pane {if $active_tab == 'style'}active{/if}" id="tab-style">
        {$form_style nofilter}
    </div>

</div>
