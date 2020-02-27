<?php

namespace App\Controllers;

use App\Response;

class MainPageController
{
    public function showMainPage() {
        return Response::response("MainPage");
    }
}