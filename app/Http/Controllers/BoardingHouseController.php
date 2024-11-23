<?php

namespace App\Http\Controllers;

use App\Interfaces\BoardingHouseRepositoryInterface;
use App\Interfaces\CategoryRepositoryInterface;
use App\Interfaces\CityRepositoryInterface;
use App\Http\Requests\HotelShowRequest;

use Illuminate\Http\Request;

class BoardingHouseController extends Controller
{
    private CityRepositoryInterface $cityRepository;
    private CategoryRepositoryInterface $categoryRepository;
    private BoardingHouseRepositoryInterface $boardingHouseRepository;

    public function __construct(
        CityRepositoryInterface $cityRepository,
        CategoryRepositoryInterface $categoryRepository,
        BoardingHouseRepositoryInterface $boardingHouseRepository
    ) {
        $this->cityRepository = $cityRepository;
        $this->categoryRepository = $categoryRepository;
        $this->boardingHouseRepository = $boardingHouseRepository;
    }

    public function show($slug)
    {
        $boardingHouse = $this->boardingHouseRepository->getBoardingHouseBySlug($slug);
        return view('pages.boarding-house.show', compact('boardingHouse'));
    }

    public function rooms($slug)
    {
        $boardingHouse = $this->boardingHouseRepository->getBoardingHouseBySlug($slug);
        return view('pages.boarding-house.rooms', compact('boardingHouse'));
    }

    public function findHotel()
    {
        $categories = $this->categoryRepository->getAllCategories();
        $cities = $this->cityRepository->getAllCities();
        return view('pages.boarding-house.find-hotel', compact('categories', 'cities'));
    }

    public function search(HotelShowRequest $request)
    {
        $searchResult = $this->boardingHouseRepository->getHotelByNameCityCategory($request->name, $request->city_id, $request->category_id);

        if (!$searchResult) {
            return redirect()->back()->with('error', 'Data Pencarian Tidak Ditemukan.');
        }

        return view('pages.boarding-house.result', compact('searchResult'));
    }
}
