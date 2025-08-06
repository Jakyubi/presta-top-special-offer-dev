{if $banners}
<div id="specialoffers-slider" class="splide">
    <div class="splide__track">
        <ul class="splide__list">
            {foreach $banners as $banner}
            <li class="splide__slide">
                <div class="special-offer-item" style="
                color:{$specialoffers_banner_text_color|escape:'html':'UTF-8'};
                background-color:{$specialoffers_banner_bg_color|escape:'html':'UTF-8'};
                text-align:center">
                    {$banner.text nofilter}
                </div>
            </li>
            {/foreach}
        </ul>
    </div>
</div>
{/if}