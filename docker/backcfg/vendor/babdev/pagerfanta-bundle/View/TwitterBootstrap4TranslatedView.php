<?php declare(strict_types=1);

namespace BabDev\PagerfantaBundle\View;

/**
 * @deprecated to be removed in BabDevPagerfantaBundle 3.0. Use the Twig view class instead with the `twitter_bootstrap4.html.twig` template.
 */
class TwitterBootstrap4TranslatedView extends TwitterBootstrapTranslatedView
{
    public function getName()
    {
        return 'twitter_bootstrap4_translated';
    }
}
