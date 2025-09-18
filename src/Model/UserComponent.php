<?php

namespace ReferralSystem\Model;

interface UserComponent
{
    public function calculatePoints(): int;
}
