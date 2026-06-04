<?php

namespace App\Http\Controllers\GeneralAffair;

use App\Helpers\UploadHelper;
use App\Http\Controllers\Controller;
use App\Models\GeneralAffairVehicle;
use App\Models\GeneralAffairVehicleService;
use Illuminate\Http\Request;

class VehicleServiceController extends Controller
{
    public function index(Request $request)
    {
        $query = GeneralAffairVehicleService::query()
            ->with('vehicle')
            ->latest('service_date');

        if ($vehicleId = $request->integer('vehicle_id')) {
            $query->where('vehicle_id', $vehicleId);
        }

        if ($dateFrom = $request->string('date_from')->toString()) {
            $query->whereDate('service_date', '>=', $dateFrom);
        }

        if ($dateTo = $request->string('date_to')->toString()) {
            $query->whereDate('service_date', '<=', $dateTo);
        }

        $reportQuery = clone $query;

        return view('pages.general-affair.vehicle-services.index', [
            'title' => 'Service Kendaraan',
            'services' => $query->paginate(10)->withQueryString(),
            'vehicles' => $this->vehicleOptions(),
            'vehicleId' => $request->integer('vehicle_id'),
            'dateFrom' => $request->string('date_from')->toString(),
            'dateTo' => $request->string('date_to')->toString(),
            'summary' => [
                'total_services' => (clone $reportQuery)->count(),
                'total_cost' => (float) (clone $reportQuery)->sum('cost'),
                'vehicles_serviced' => (clone $reportQuery)->distinct('vehicle_id')->count('vehicle_id'),
                'upcoming_services' => GeneralAffairVehicleService::query()
                    ->whereNotNull('next_service_date')
                    ->whereDate('next_service_date', '>=', now()->toDateString())
                    ->whereDate('next_service_date', '<=', now()->addDays(30)->toDateString())
                    ->count(),
            ],
        ]);
    }

    public function create(Request $request)
    {
        return view('pages.general-affair.vehicle-services.create', [
            'title' => 'Input Service Kendaraan',
            'vehicles' => $this->vehicleOptions(),
            'selectedVehicleId' => $request->integer('vehicle_id'),
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validatedData($request);
        $data['cost'] ??= 0;

        if ($request->hasFile('document')) {
            $data['document_path'] = UploadHelper::store($request->file('document'), 'general-affair/vehicle-services');
        }

        GeneralAffairVehicleService::create($data);

        return redirect()->route('general-affair.vehicle-services.index')->with('success', 'Rekap service berhasil dibuat.');
    }

    public function edit(GeneralAffairVehicleService $vehicleService)
    {
        return view('pages.general-affair.vehicle-services.edit', [
            'title' => 'Edit Service Kendaraan',
            'service' => $vehicleService,
            'vehicles' => $this->vehicleOptions(),
            'selectedVehicleId' => $vehicleService->vehicle_id,
        ]);
    }

    public function update(Request $request, GeneralAffairVehicleService $vehicleService)
    {
        $data = $this->validatedData($request);
        $data['cost'] ??= 0;

        if ($request->hasFile('document')) {
            UploadHelper::delete($vehicleService->document_path);
            $data['document_path'] = UploadHelper::store($request->file('document'), 'general-affair/vehicle-services');
        }

        $vehicleService->update($data);

        return redirect()->route('general-affair.vehicle-services.index')->with('success', 'Rekap service berhasil diperbarui.');
    }

    public function destroy(GeneralAffairVehicleService $vehicleService)
    {
        UploadHelper::delete($vehicleService->document_path);
        $vehicleService->delete();

        return redirect()->route('general-affair.vehicle-services.index')->with('success', 'Rekap service berhasil dihapus.');
    }

    private function validatedData(Request $request): array
    {
        return $request->validate([
            'vehicle_id' => ['required', 'exists:general_affair_vehicles,id'],
            'service_date' => ['required', 'date'],
            'service_type' => ['required', 'string', 'max:100'],
            'workshop_name' => ['nullable', 'string', 'max:150'],
            'odometer' => ['nullable', 'integer', 'min:0'],
            'cost' => ['nullable', 'numeric', 'min:0'],
            'description' => ['nullable', 'string'],
            'next_service_date' => ['nullable', 'date'],
            'next_service_odometer' => ['nullable', 'integer', 'min:0'],
            'document' => ['nullable', 'file', 'max:4096'],
        ]);
    }

    private function vehicleOptions()
    {
        return GeneralAffairVehicle::query()
            ->where('ownership_status', 'asset')
            ->orderBy('plate_number')
            ->get(['id', 'plate_number', 'vehicle_type', 'brand']);
    }
}
