<?php

namespace App\Twig;

use Symfony\Component\HttpFoundation\RequestStack;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class ActiveExtension extends AbstractExtension
{
    /**
     * @var RequestStack
     */
    protected $requests;

    public function __construct(RequestStack $requests)
    {
        $this->requests = $requests;
    }

    public function activeClass(string $condition, string $class = 'active'): ?string
    {
        $request = $this->requests->getCurrentRequest();
        $subject = $request->get('_route');
        if ($subject === $condition) {
            return $class;
        }
        if (($subject !== null) && preg_match("#^$condition$#", $subject)) {
            return $class;
        }
        $subject = $request->getPathInfo();
        if ($subject === $condition) {
            return $class;
        }
        if (($subject !== null) && preg_match("#^$condition$#", $subject)) {
            return $class;
        }
        return null;
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('active', [$this, 'activeClass']),
        ];
    }
}
