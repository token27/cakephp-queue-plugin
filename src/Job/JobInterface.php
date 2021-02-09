<?php

namespace Queue\Transport;

/**
 * Any task needs to at least implement run().
 * The add() is mainly only for CLI adding purposes and optional.
 *
 * Either throw an exception with an error message, or use $this->abort('My message'); to fail a job.
 *
 */
interface JobInterface {

    /**
     * Push the job into the queue
     *
     * @return bool
     */
    public function push(): bool;

    /**
     * 
     * @param type $content
     * @return \Queue\Transport\JobInterface
     */
    public function send($content = null): JobInterface;
//
//    /**
//     *  Run the job 
//     * 
//     * @return \Queue\Transport\JobInterface
//     */
    public function run(): void;

    /**
     * Get locale used for the notification
     *
     * @return string|null
     */
    public function getLocale(): ?string;

    /**
     * Set locale used for the notification
     *
     * @param string $locale The name of the locale to set
     * @return self
     */
    public function setLocale(string $locale = null): JobInterface;

    /**
     * Get before send callback.
     *
     * @return array
     */
    public function getBeforeSendCallback(): array;

    /**
     * Set before send callback.
     *
     * @param string|array|null $class Name of the class and method
     * - Pass a string in the class::method format to call a static method
     * - Pass an array in the [class => method] format to call a non static method
     * @param array $args the method parameters you want to pass to the called method
     * @return self
     */
    public function setBeforeSendCallback($class = null, array $args = []): JobInterface;

    /**
     * Get after send callback.
     *
     * @return array
     */
    public function getAfterSendCallback(): array;

    /**
     * Set after send callback.
     *
     * @param string|array|null $class Name of the class and method
     * - Pass a string in the class::method format to call a static method
     * - Pass an array in the [class => method] format to call a non static method
     * @param array $args the method parameters you want to pass to the called method
     * @return self
     */
    public function setAfterSendCallback($class = null, array $args = []): JobInterface;
}
