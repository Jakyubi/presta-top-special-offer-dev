<style>
td{
    padding:2px 5px;
}

th{
    padding:2px 5px;
}
</style>
<ul class="nav nav-tabs" role="tablist">
    <li class="{if $active_tab == 'settings'}active{/if}">
        <a href="#tab-settings" data-toggle="tab">{l s='Settings' mod='specialoffers'}</a>
    </li>
    <li class="{if $active_tab == 'style'}active{/if}">
        <a href="#tab-style" data-toggle="tab">{l s='Style' mod='specialoffers'}</a>
    </li>
</ul>
<div class="tab-content" >
    <div class="tab-pane {if $active_tab == 'settings'}active{/if}" id="tab-settings">
        {$form_settings nofilter}
    </div>
    <div class="tab-pane {if $active_tab == 'style'}active{/if}" id="tab-style">
        {$form_style nofilter}
    </div>
</div>

<div>
<table>
    <tr> 
        <th>id</th>
        <th>text</th>
        <th>date start</th>
        <th>date end</th>
        <th>enabled</th>
        <th></th>
        <th></th>
    </tr>
    {foreach from=$banners item=$banner}
        <div>
        <tr>
                <td>{$banner.id_banner|escape:'html':'UTF-8'}</td>
                <td>{$banner.text|escape:'html':'UTF-8'}</td>
                <td>{$banner.date_start|escape:'html':'UTF-8'}</td>
                <td>{$banner.date_end|escape:'html':'UTF-8'}</td>
                <td style="text-align:center">{$banner.enabled|escape:'html':'UTF-8'}</td>

                <td>
                <a href="{$link->getAdminLink('AdminModules', true, [], [
                    'configure' => $module->name, 'editBanner' => $banner.id_banner])}">
                    {l s='Edit' mod='specialoffers'}</a>
                </td>

                <td>
                <a href="{$link->getAdminLink('AdminModules', true, [], [
                    'configure' => $module->name, 'deleteBanner' => $banner.id_banner])}">
                    {l s='Delete' mod='specialoffers'}</a>
                </td>
        </tr>
        </div>
    {/foreach}
</table>
</div>