{extends file='parent:checkout/cart.tpl'}

{block name='content'}
  {* Standard hook usually available in the cart footer *}
  {$smarty.block.parent}
  {hook h='displayProductListFunctionalButtons'}
  
{/block}
