<?php

/*
 * This file is part of the "headless" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 *
 * (c) 2021
 */

declare(strict_types=1);

namespace FriendsOfTYPO3\Headless\Dto;

use TYPO3\CMS\Core\Http\ServerRequest;
use TYPO3\CMS\Core\Site\Entity\NullSite;
use TYPO3\CMS\Core\Site\Entity\Site;
use TYPO3\CMS\Core\Site\SiteFinder;
use TYPO3\CMS\Core\Utility\GeneralUtility;

final class JsonViewDemandDto implements JsonViewDemandDtoInterface
{
    private int $pageId = 0;
    private Site $site;
    private \TYPO3\CMS\Core\Site\Entity\SiteLanguage $siteLanguage;
    private int $feGroup = 0;
    private bool $hiddenContentVisible = true;
    private string $pageTypeMode = 'default';
    private string $pluginNamespace;
    private bool $initialized = false;

    /**
     * @param ServerRequest $request
     * @param string $pluginNamespace
     * @throws \TYPO3\CMS\Core\Exception\SiteNotFoundException
     */
    public function __construct(ServerRequest $request, string $pluginNamespace = '')
    {
        $this->pluginNamespace = $pluginNamespace;
        $site = $request->getAttribute('site');

        if (($site === null || $site instanceof NullSite) && $this->getActionArgument($request, 'site') !== null) {
            /** @var SiteFinder $siteFinder */
            $siteFinder = GeneralUtility::makeInstance(SiteFinder::class);
            $site = $siteFinder->getSiteByIdentifier($this->getActionArgument($request, 'site', ''));
        }

        if ($site instanceof Site) {
            $this->site = $site;
            $this->pageId = (int)$this->getActionArgument($request, 'id', 0);
            $this->feGroup = (int)$this->getActionArgument($request, 'feGroup');
            $this->hiddenContentVisible = (bool)$this->getActionArgument($request, 'hidden', true);
            $this->pageTypeMode = (string)$this->getActionArgument($request, 'pageTypeMode', 'default');

            if ($this->site->getLanguages()) {
                $lang = (int)$this->getActionArgument($request, 'lang', 0);
                foreach ($this->site->getLanguages() as $language) {
                    if ($language->getLanguageId() === $lang) {
                        $this->siteLanguage = $language;
                        break;
                    }
                }
            }

            $this->initialized = true;
        }
    }

    /**
     * @param $request
     * @param string $argumentName
     * @param $defaultValue
     * @return mixed
     */
    protected function getActionArgument($request, string $argumentName, $defaultValue = null)
    {
        return $request->getParsedBody()[$argumentName]
            ?? $request->getQueryParams()[$argumentName]
            ?? $request->getQueryParams()[$this->pluginNamespace][$argumentName]
            ?? $defaultValue;
    }

    /**
     * @return int
     */
    public function getPageId(): int
    {
        return $this->pageId;
    }

    /**
     * @return \TYPO3\CMS\Core\Site\Entity\Site
     */
    public function getSite(): \TYPO3\CMS\Core\Site\Entity\Site
    {
        return $this->site;
    }

    /**
     * @return \TYPO3\CMS\Core\Site\Entity\SiteLanguage
     */
    public function getSiteLanguage(): \TYPO3\CMS\Core\Site\Entity\SiteLanguage
    {
        return $this->siteLanguage;
    }

    /**
     * @return int
     */
    public function getFeGroup(): int
    {
        return $this->feGroup;
    }

    /**
     * @return bool
     */
    public function isHiddenContentVisible(): bool
    {
        return $this->hiddenContentVisible;
    }

    /**
     * @return string
     */
    public function getPageTypeMode(): string
    {
        return $this->pageTypeMode;
    }

    /**
     * @return int
     */
    public function getLanguageId(): int
    {
        return $this->getSiteLanguage()->getLanguageId();
    }

    /**
     * @return string
     */
    public function getPluginNamespace(): string
    {
        return $this->pluginNamespace;
    }

    /**
     * @return array
     */
    public function getCurrentDemandArgumentsAsArray(): array
    {
        return [
            'pageType' => $this->getPageTypeMode(),
            'lang' => $this->getLanguageId(),
            'id' => $this->getPageId(),
            'feGroup' => $this->getFeGroup(),
            'site' => $this->getSite()->getIdentifier(),
            'hidden' => $this->isHiddenContentVisible()
        ];
    }

    /**
     * @return bool
     */
    public function isInitialized(): bool
    {
        return $this->initialized;
    }
}
