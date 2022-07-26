<?php

namespace MUtil\Model\Transform;

class TranslateFieldNames extends \MUtil_Model_ModelTransformerAbstract
{
    /**
     * @var array List of field translations. Key is the name in the database, value is the translated name
     */
    private array $fieldTranslations;

    /**
     * @var bool should the old field names be removed, or kept in the returned array?
     */
    private bool $removeOldFieldNames;

    public function __construct(array $fieldTranslations, bool $removeOldFieldNames = true)
    {
        $this->fieldTranslations = $fieldTranslations;
        $this->removeOldFieldNames = $removeOldFieldNames;
    }

    public function transformLoad(\MUtil_Model_ModelAbstract $model, array $data, $new = false, $isPostData = false): array
    {
        foreach($data as $key => $row) {
            foreach ($this->fieldTranslations as $oldFieldName => $newFieldName) {
                if (array_key_exists($oldFieldName, $row)) {
                    $data[$key][$newFieldName] = $row[$oldFieldName];
                    if ($this->removeOldFieldNames) {
                        unset($data[$key][$oldFieldName]);
                    }
                }
            }
        }

        return $data;
    }

    public function transformRowBeforeSave(\MUtil_Model_ModelAbstract $model, array $row): array
    {
        foreach ($this->fieldTranslations as $newFieldName => $oldFieldName) {
            if (array_key_exists($oldFieldName, $row)) {
                $row[$newFieldName] = $row[$oldFieldName];
                if ($this->removeOldFieldNames) {
                    unset($row[$oldFieldName]);
                }
            }
        }
        return $row;
    }
}