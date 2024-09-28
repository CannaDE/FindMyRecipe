<?php
namespace fmr;

use fmr\system\util\StringUtil;

class MetaTagHandler extends Singleton implements \Countable, \Iterator {

    protected int $index = 0;

    protected int|array $indexToObject = [];

    protected array $objects = [];

    protected function init(): void {

        if($value = META_DESCRIPTION)
            $this->addTag('description', 'description', $value);

        if($value = PAGE_TITLE)
            $this->addTag('og:site_name', 'og:site_name', $value, true);

        $this->addTag('apple-mobile-web-app-status-bar-style', 'apple-mobile-web-app-status-bar-style', 'black-translucent');
        $this->addTag('apple-mobile-web-app-capable', 'apple-mobile-web-app-capable', 'yes');
        /*if(OG_IMAGE) {
            $this->addTag(
                'og:image',
                'og:image',
                (preg_match('~^https?://~', OG_IMAGE) ? OG_IMAGE : TimeMonitoring::getPath() . OG_IMAGE),
                true
            );
        }*/
    }

    public function addTag(string $identifier, string $name, string $value, bool $isProperty = false): void {

        if(!isset($this->objects[$identifier]))
            $this->indexToObject[] = $identifier;

        $this->objects[$identifier] = [
            'isProperty' => $isProperty,
            'name' => $name,
            'value' => $value,
        ];

        if($name == 'og:description' && $value)
            $this->addTag('description', 'description', $value);
    }

    public function removeTag(string $identifier): void {

        if(isset($this->objects[$identifier])) {
            unset($this->objects[$identifier]);

            $this->indexToObject = array_keys($this->objects);
        }
    }

    /**
     * @inheritDoc
     */
    public function current(): string {

        $tag = $this->objects[$this->indexToObject[$this->index]];

        return '<meta ' . ($tag['isProperty'] ? 'property' : 'name') . '="' . $tag['name'] . '" content="' . StringUtil::encodeHTML($tag['value']) . '">';
    }

    /**
     * @inheritDoc
     */
    public function next(): void {
        $this->index++;
    }

    /**
     * @inheritDoc
     */
    public function key(): string {
        return $this->indexToObject[$this->index];
    }

    /**
     * @inheritDoc
     */
    public function valid(): bool {
        return isset($this->indexToObject[$this->index]);
    }

    /**
     * @inheritDoc
     */
    public function rewind(): void {
        $this->index = 0;
    }

    /**
     * @inheritDoc
     */
    public function count(): int {
        return count($this->objects);
    }
}