<?php declare(strict_types=1);

namespace BabDev\PagerfantaBundle\View;

use Pagerfanta\PagerfantaInterface;
use Pagerfanta\Twig\View\TwigView;
use Pagerfanta\View\ViewInterface;
use Symfony\Component\Translation\TranslatorInterface as LegacyTranslatorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @deprecated to be removed in BabDevPagerfantaBundle 3.0. Use the Twig view class instead.
 */
abstract class TranslatedView implements ViewInterface
{
    /**
     * @var ViewInterface
     */
    private $view;

    /**
     * @var LegacyTranslatorInterface|TranslatorInterface
     */
    private $translator;

    public function __construct(ViewInterface $view, object $translator)
    {
        if (!($translator instanceof LegacyTranslatorInterface) && !($translator instanceof TranslatorInterface)) {
            throw new \InvalidArgumentException(sprintf('The $translator argument of %s must be an instance of %s or %s, a %s was given.', static::class, LegacyTranslatorInterface::class, TranslatorInterface::class, \get_class($translator)));
        }

        $this->view = $view;
        $this->translator = $translator;
    }

    public function render(PagerfantaInterface $pagerfanta, $routeGenerator, array $options = [])
    {
        trigger_deprecation('babdev/pagerfanta-bundle', '2.2', 'The "%s" class is deprecated and will be removed in 3.0. Use the "%s" class instead.', static::class, TwigView::class);

        $optionsWithTranslations = $this->addTranslationOptions($options);

        return $this->view->render($pagerfanta, $routeGenerator, $optionsWithTranslations);
    }

    private function addTranslationOptions($options)
    {
        return $this->addNextTranslationOption(
            $this->addPreviousTranslationOption($options)
        );
    }

    private function addPreviousTranslationOption($options)
    {
        return $this->addTranslationOption($options, $this->previousMessageOption(), 'previousMessage');
    }

    private function addNextTranslationOption($options)
    {
        return $this->addTranslationOption($options, $this->nextMessageOption(), 'nextMessage');
    }

    private function addTranslationOption($options, $option, $messageMethod)
    {
        if (isset($options[$option])) {
            return $options;
        }

        $message = $this->$messageMethod();

        return array_merge($options, [$option => $message]);
    }

    abstract protected function previousMessageOption();

    abstract protected function nextMessageOption();

    private function previousMessage()
    {
        $previousText = $this->previousText();

        return $this->buildPreviousMessage($previousText);
    }

    private function nextMessage()
    {
        $nextText = $this->nextText();

        return $this->buildNextMessage($nextText);
    }

    private function previousText()
    {
        return $this->translator->trans('previous', [], 'pagerfanta');
    }

    private function nextText()
    {
        return $this->translator->trans('next', [], 'pagerfanta');
    }

    abstract protected function buildPreviousMessage($text);

    abstract protected function buildNextMessage($text);
}
