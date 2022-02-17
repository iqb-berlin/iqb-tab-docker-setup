<?php
declare(strict_types=1);
// TODO unit-tests

class SessionChangeMessage implements JsonSerializable {

    protected int $personId;
    protected int $timestamp = 0;
    protected ?int $testId;
    protected ?string $groupName;
    protected ?string $groupLabel = "";
    protected ?string $personLabel = "";
    protected ?string $mode = "";
    protected ?array $testState = [];
    protected ?string $bookletName = "";
    protected ?string $unitName = "";
    protected ?array $unitState = [];


    private function __construct(int $personId, int $testId, int $timestamp = null) {

        $this->personId = $personId;
        $this->testId = $testId;

        $this->timestamp = $timestamp ?? TimeStamp::now();
    }


    public static function session(int $testId, PersonSession $session, int $timestamp = null): SessionChangeMessage {

        $message = new SessionChangeMessage($session->getPerson()->getId(), $testId, $timestamp);
        $message->setSession($session);
        return $message;
    }


    public static function testState(int $personId, int $testId, array $testState, string $bookletName = null): SessionChangeMessage {

        $message = new SessionChangeMessage($personId, $testId);
        $message->setTestState($testState, $bookletName);
        return $message;
    }


    public static function unitState(int $personId, int $testId, string $unitName, array $unitState): SessionChangeMessage {

        $message = new SessionChangeMessage($personId, $testId);
        $message->setUnitState($unitName, $unitState);
        return $message;
    }


    public function setSession(PersonSession $session): void {

        $login = $session->getLoginSession()->getLogin();
        $code = $session->getPerson()->getCode();

        $this->personLabel = $login->getName() . ($code ? '/' . $code : '');

        $this->mode = $login->getMode();
        $this->groupName = $login->getGroupName();
        $this->groupLabel = $login->getGroupLabel();
    }


    public function setTestState(array $testState, string $bookletName = null): void {

        $this->testState = $testState;
        if ($bookletName !== null) {
            $this->bookletName = $bookletName;
        }
    }

    public function setUnitState(string $unitName, array $unitState) {

        $this->unitName = $unitName;
        $this->unitState = $unitState;
    }


    public function jsonSerialize() {

        $jsonData = [];

        foreach ($this as $key => $value) {

            if ($value !== "") {
                $jsonData[$key] = $value;
            }
        }

        return $jsonData;
    }
}