<style>
td{
    padding:2px 5px;
}

th{
    padding:2px 5px;
}
</style>



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
            <div class="panel">
            <!-- banner list -->
            <table>
                <tr>  
                    <th>id</th>
                    <th>id group</th>
                    <th>id lang</th>
                    <th>text</th>
                    <th>date start</th>
                    <th>date end</th>
                    <th>enabled</th>
                    <th colspan="2">{l s='actions' mod='specialoffers'}</th>

                </tr>


                {foreach from=$banners item=$banner}
                    <tr>                
                        <td>{$banner.id_banner|escape:'html':'UTF-8'}</td>
                        <td>{$banner.id_group|escape:'html':'UTF-8'}</td>
                        <td>{$banner.id_lang|escape:'html':'UTF-8'}</td>
                        <td>{$banner.text|escape:'html':'UTF-8'}</td>
                        <td>{$banner.date_start|escape:'html':'UTF-8'}</td>
                        <td>{$banner.date_end|escape:'html':'UTF-8'}</td>
                        <td style="text-align:center">{$banner.enabled|escape:'html':'UTF-8'}</td>

                        <td>
                            <a class="btn btn-sm btn-primary" href="{$link->getAdminLink('AdminModules', true, [], [
                                'configure' => $module->name, 'editBanner' => $banner.id_group])}">
                                {l s='Edit' mod='specialoffers'}
                            </a>
                        </td>

                        <td>
                            <a class="btn btn-sm btn-danger" href="{$link->getAdminLink('AdminModules', true, [], [
                                'configure' => $module->name, 'deleteBanner' => $banner.id_group])}"
                                onclick="return confirm('{l s='Are you sure you want to delete this banner?' mod='specialoffers'}');">
                                {l s='Delete' mod='specialoffers'}
                            </a>
                        </td>
                    </tr>
                {/foreach}
            </table>

                <form method="post" style="margin-top:10px;">
                    <input type="hidden" name="showAddForm" value="1">
                    <button type="submit" class="btn btn-success">{l s='Add New Banner' mod='specialoffers'}</button>
                </form>

            </div>
        {/if}
    </div>


    <!-- style form -->
    <div class="tab-pane {if $active_tab == 'style'}active{/if}" id="tab-style">
        {$form_style nofilter}
    </div>

</div>
