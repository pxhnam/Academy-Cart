<?php

namespace App\Repositories;

use Illuminate\Cache\Repository;
use Illuminate\Support\Facades\Http;
use App\Repositories\Interfaces\CourseRepositoryInterface;

class CourseRepository implements CourseRepositoryInterface
{

    private $apiCourse;

    public function __construct()
    {
        $this->apiCourse = config('services.api.courses');
    }

    public function list()
    {
        $data = Http::get($this->apiCourse);
        if ($data->successful()) {
            return $data->json();
        }
    }

    public function find($id)
    {
        $data = Http::get($this->apiCourse . 'find/' . $id);
        if ($data->successful()) {
            return $data->json();
        }
    }
    public function check($id)
    {
        $data = Http::get($this->apiCourse . 'check/' . $id);
        if ($data->successful()) {
            return $data->json();
        }
    }
}
