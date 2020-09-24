<?php
/** @noinspection PhpUnhandledExceptionInspection */
declare(strict_types=1);


class XMLFileUnit extends XMLFile {

    protected int $totalSize = 0;
    protected string $playerId = '';


    public function setTotalSize(WorkspaceValidator $validator): void {

        $this->totalSize = $this->size;

        $definitionRef = $this->getDefinitionRef();

        if (!$definitionRef) {
            return;
        }

        if ($validator->resourceExists($definitionRef, false)) {
            $this->totalSize += $validator->allResourceFilesWithSize[FileName::normalize($definitionRef, false)];
        } else {
            $this->report('error', "definitionRef `$definitionRef` not found");
        }
    }


    public function getTotalSize(): int {

        return $this->totalSize;
    }


    public function setPlayerId(WorkspaceValidator $validator): void {

        $playerId = strtoupper($this->getPlayer());
        if (strlen($playerId) > 0) {
            if (substr($playerId, -5) != '.HTML') {
                $playerId = $playerId . '.HTML';
            }
            if (!$validator->resourceExists($playerId, true)) {
                $this->report('error', "unit definition type `$playerId` not found"); // TODO better msg
            }
            $this->playerId = $playerId;
        } else {
            $this->report('error', "no player defined");
        }
    }


    public function getPlayerId(): string {

        return $this->playerId;
    }



    private function getPlayer() {
        $myreturn = '';
        if ($this->isValid() and ($this->xmlfile != false) and ($this->rootTagName == 'Unit')) {
            $definitionNode = $this->xmlfile->Definition[0];
            if (isset($definitionNode)) {
                $playerAttr = $definitionNode['player'];
                if (isset($playerAttr)) {
                    $myreturn = (string) $playerAttr;
                }
            } else {
                $definitionNode = $this->xmlfile->DefinitionRef[0];
                if (isset($definitionNode)) {
                    $playerAttr = $definitionNode['player'];
                    if (isset($playerAttr)) {
                        $myreturn = (string) $playerAttr;
                    }
                }
            }
        }
        return $myreturn;
    }


    private function getDefinitionRef(): string {

        $myreturn = '';
        if ($this->isValid() and ($this->xmlfile != false) and ($this->rootTagName == 'Unit')) {
            $definitionNode = $this->xmlfile->DefinitionRef[0];
            if (isset($definitionNode)) {
                $rFilename = (string) $definitionNode;
                if (isset($rFilename)) {
                    $myreturn = $rFilename;
                }
            }
        }
        return $myreturn;
    }
}
