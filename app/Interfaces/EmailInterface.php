<?php

namespace App\Interfaces;

interface EmailInterface
{
    public function getEmailByRapidxUserId($userId);
    public function ecrEmailMsg($ecrsId);

    public function ecrEmailMsgEcrRequirement($ecrsId);

    public function materialEmailMsg($selectedId);

    public function ecrEmailMsgWithStatus(array $data);
    public function sendEmail(array $data);
    public function sendEmailWithAttachment(array $data);
    public function sendEmailWithSchedule(array $data);
}
