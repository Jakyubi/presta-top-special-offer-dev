{if $banners}
<style>
    .special-offer-item{
        background-color: {$specialoffers_banner_bg_color|escape:'html':'UTF-8'};
        text-align:center;
        width: {$specialoffers_banner_width|escape:'html':'UTF-8'}{$specialoffers_banner_unit|escape:'html':'UTF-8'};
        height: {$specialoffers_banner_height|escape:'html':'UTF-8'}{$specialoffers_banner_unit|escape:'html':'UTF-8'};
    }
    .special-offer-item * {
        color: {$specialoffers_banner_text_color|escape:'html':'UTF-8'};
    }
    .splide__slide{
        display: flex;
        justify-content: center;
    }

</style>

<div id="specialoffers-slider" class="splide">
    <div class="splide__track">
        <ul class="splide__list">
            {foreach $banners as $banner}
            <li class="splide__slide">
                <div class="special-offer-item">
                {if $banner.link|trim}
                    <a href="{$banner.link|escape:'html' : 'UTF-8'}">
                        {$banner.text nofilter}
                    </a>
                {else}
                    {$banner.text nofilter}
                {/if}
                </div>
            </li>
            {/foreach}
        </ul>
    </div>
</div>
{/if}