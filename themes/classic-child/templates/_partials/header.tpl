{extends file='parent:_partials/header.tpl'}

{block name='header_nav'}
  <nav class="header-nav">
  <div class="col-md-6 left-nav">
  {hook h='displayNav1'}

  </div>
    <div class="col-md-6 right-nav">
        {hook h='displayNav2'}
        
        <div class="wishlist-link-wrapper">
            <a href="{$link->getModuleLink('blockwishlist', 'lists')}">
                <i class="material-icons">favorite_border</i>
                <span class="hidden-sm-down">{l s='Wishlist' d='Shop.Theme.Actions'}</span>
            </a>
        </div>
    </div>
  </nav>
{/block}
