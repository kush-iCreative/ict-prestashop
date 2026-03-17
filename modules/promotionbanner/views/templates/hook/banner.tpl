<!-- Swiper 7 Assets -->
<link rel="stylesheet" href="https://unpkg.com/swiper@7/swiper-bundle.min.css"/>
<script src="https://unpkg.com/swiper@7/swiper-bundle.min.js"></script>
{if $banners}
<div class="swiper" style="width:100%; height: 400px;"> {* Add a height for testing *}
    <div class="swiper-wrapper">
        {foreach from=$banners item=banner}
            {if is_array($banner.image)}
                {foreach from=$banner.image item=imgName}
                    <div class="swiper-slide">
                        <img src="{$banner_img_path}{$imgName}" 
                             alt="{$banner.title|escape:'html':'UTF-8'}" 
                             style="width:100%; height:100%; object-fit: cover;">
                        
                        <div class="carousel-caption carousel-setting" >
                            <h2>{$banner.title|escape:'html':'UTF-8'}</h2>
                            <div class='font-div'>{$banner.description nofilter}</div>
                            {if $banner.cta_link}
                                <a href="{$banner.cta_link}" class="btn btn-primary btn-custom">Shop Now</a>
                            {/if}
                        </div>
                    </div>
                {/foreach}
            {/if}
        {/foreach}
    </div>
    <div class="swiper-pagination"></div>
    <div class="swiper-button-next"></div>
    <div class="swiper-button-prev"></div>
</div>
{/if}