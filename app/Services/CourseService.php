<?php

namespace App\Services;

use App\Repositories\Interfaces\CourseRepositoryInterface;
use App\Services\Interfaces\CourseServiceInterface;

class CourseService implements CourseServiceInterface
{
    private $courseRepository;
    public function __construct(CourseRepositoryInterface $courseRepository)
    {
        $this->courseRepository = $courseRepository;
    }
    public function get()
    {
        return $this->courseRepository->list();
    }
    public function find($id)
    {
        return $this->courseRepository->find($id);
    }
    public function check($id)
    {
        return $this->courseRepository->check($id);
    }
}
