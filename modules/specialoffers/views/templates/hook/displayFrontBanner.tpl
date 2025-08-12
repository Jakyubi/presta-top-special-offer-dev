{if $banners}
<style>
    .special-offer-item{
        color: {$specialoffers_banner_text_color|escape:'html':'UTF-8'} !important;
        background-color: {$specialoffers_banner_bg_color|escape:'html':'UTF-8'} !important;
        text-align:center;
    }
    .special-offer-item * {
    color: {$specialoffers_banner_text_color|escape:'html':'UTF-8'} !important;
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