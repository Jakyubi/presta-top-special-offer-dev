{if $banners}
<div class="special-offers-wrapper">
    {foreach $banners as $banner}
        <div class="special-offer-item" style="
        color:{$specialoffers_text_color|escape:'html':'UTF-8'};
        background-color:{$specialoffers_bg_color|escape:'html':'UTF-8'};
        text-align:center">
            {$banner.text nofilter}
        </div>
    {/foreach}
</div>
{/if}
