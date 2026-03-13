{extends file='parent:catalog/product.tpl'}

{block name='product_additional_info'}

  {$smarty.block.parent}

  <div class="product-custom mt-4" >
    <a href="{$urls.pages.index}" class="btn btn-primary">
      {l s='Back to Home' d='Shop.Theme.Global'}
    </a>
  </div>

  <div class="custom-hook mt-2">
  {hook h='displayCustomHook'}
  </div>
{/block}
