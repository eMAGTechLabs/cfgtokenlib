<?php

namespace ConfigToken\File\ConnectionSettings\Types;


use ConfigToken\Options\Exceptions\UnknownOptionValueException;

class GitRepoFileClientConnectionSettings extends LocalFileClientConnectionSettings
{
    const GROUP_NAME = 'group';
    const REPO_NAME = 'repo';
    const BRANCH_NAME = 'branch';
    const TAG_NAME = 'tag';
    const COMMIT_HASH = 'commit';

    protected function initialize()
    {
        parent::initialize();
        $this->setDirectorySeparator('/');
        $this->setRootPath('/');
        $this->setRequiredKey(static::GROUP_NAME);
        $this->setRequiredKey(static::REPO_NAME);
        $this->setRequiredKey(static::BRANCH_NAME);
        $this->setRequiredKey(static::TAG_NAME);
        $this->setOptionalKey(static::COMMIT_HASH);
    }

    public function getRequiredKeysWithoutValues($useDefaultsIfMissing=false)
    {
        $missingKeys = parent::getRequiredKeysWithoutValues($useDefaultsIfMissing);
        if ((($branchKey = array_search(static::BRANCH_NAME, $missingKeys)) !== false) &&
            !in_array(static::TAG_NAME, $missingKeys)) {
            unset($missingKeys[$branchKey]);
        }
        if ((($tagKey = array_search(static::TAG_NAME, $missingKeys)) !== false) &&
            !in_array(static::BRANCH_NAME, $missingKeys)) {
            unset($missingKeys[$tagKey]);
        }
    }

    /**
     * Check if the group name was set.
     *
     * @return boolean
     */
    public function hasGroupName()
    {
        return $this->hasValue(self::GROUP_NAME);
    }

    /**
     * Get the group name.
     *
     * @throws UnknownOptionValueException
     * @return string
     */
    public function getGroupName()
    {
        return $this->getValue(self::GROUP_NAME, true);
    }

    /**
     * Set the group name.
     *
     * @param string $value The new value.
     * @return $this
     */
    public function setGroupName($value)
    {
        return $this->setValue(self::GROUP_NAME, $value);
    }

    /**
     * Check if the repo name was set.
     *
     * @return boolean
     */
    public function hasRepoName()
    {
        return $this->hasValue(self::REPO_NAME);
    }

    /**
     * Get the repo name.
     *
     * @throws UnknownOptionValueException
     * @return string
     */
    public function getRepoName()
    {
        return $this->getValue(self::REPO_NAME, true);
    }

    /**
     * Set the repo name.
     *
     * @param string $value The new value.
     * @return $this
     */
    public function setRepoName($value)
    {
        return $this->setValue(self::REPO_NAME, $value);
    }

    /**
     * Check if the branch name was set.
     *
     * @return boolean
     */
    public function hasBranchName()
    {
        return $this->hasValue(self::BRANCH_NAME);
    }

    /**
     * Get the branch name.
     *
     * @throws UnknownOptionValueException
     * @return string
     */
    public function getBranchName()
    {
        return $this->getValue(self::BRANCH_NAME, true);
    }

    /**
     * Set the branch name.
     *
     * @param string $value The new value.
     * @return $this
     */
    public function setBranchName($value)
    {
        return $this->setValue(self::BRANCH_NAME, $value);
    }

    /**
     * Check if the tag name was set.
     *
     * @return boolean
     */
    public function hasTagName()
    {
        return $this->hasValue(self::TAG_NAME);
    }

    /**
     * Get the tag name.
     *
     * @throws UnknownOptionValueException
     * @return string|null
     */
    public function getTagName()
    {
        return $this->getValue(self::TAG_NAME, true);
    }

    /**
     * Set the tag name.
     *
     * @param string $value The new value.
     * @return $this
     */
    public function setTagName($value)
    {
        return $this->setValue(self::TAG_NAME, $value);
    }

    /**
     * Check if the commit hash was set.
     *
     * @return boolean
     */
    public function hasCommitHash()
    {
        return $this->hasValue(self::COMMIT_HASH);
    }

    /**
     * Get the commit hash.
     *
     * @throws UnknownOptionValueException
     * @return string
     */
    public function getCommitHash()
    {
        return $this->getValue(self::COMMIT_HASH, true);
    }

    /**
     * Set the commit hash.
     *
     * @param string $value The new value.
     * @return $this
     */
    public function setCommitHash($value)
    {
        return $this->setValue(self::COMMIT_HASH, $value);
    }
}