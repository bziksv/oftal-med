<?php

namespace Skyweb24\ChatgptSeo\Service\TaskFormField\Option\Incorrect;

class IncorrectPatternProperty extends IncorrectPattern
{
    protected ?string $type = null;
    protected ?string $iblockId = null;
    protected array $optionList;

    public function __construct(
        ?string $name = 'incorrect',
        ?string $code = 'incorrect',
        ?string $type = null,
        ?string $iblockId = null,
        array $optionList = [],
    )
    {
        parent::__construct($name, $code);
        $this->type = $type;
        $this->iblockId = $iblockId;
        $this->optionList = $optionList;
    }

    /**
     * @return array
     */
    public function getOptionList(): array
    {
        return $this->optionList;
    }

    /**
     * @param array $optionList
     * @return IncorrectPatternProperty
     */
    public function setOptionList(array $optionList): IncorrectPatternProperty
    {
        $this->optionList = $optionList;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getType(): ?string
    {
        return $this->type;
    }

    /**
     * @param string|null $type
     * @return IncorrectPatternProperty
     */
    public function setType(?string $type): IncorrectPatternProperty
    {
        $this->type = $type;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getIblockId(): ?string
    {
        return $this->iblockId;
    }

    /**
     * @param string|null $iblockId
     * @return IncorrectPatternProperty
     */
    public function setIblockId(?string $iblockId): IncorrectPatternProperty
    {
        $this->iblockId = $iblockId;
        return $this;
    }


}