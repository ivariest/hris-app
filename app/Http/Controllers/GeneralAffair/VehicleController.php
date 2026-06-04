<?php

namespace App\Http\Controllers\GeneralAffair;

use App\Helpers\UploadHelper;
use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\GeneralAffairVehicle;
use App\Models\Location;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class VehicleController extends Controller
{
    public function index(Request $request)
    {
        $query = GeneralAffairVehicle::query()
            ->with(['location', 'driver'])
            ->withCount('services')
            ->latest();

        if ($search = trim($request->string('search')->toString())) {
            $query->where(function ($builder) use ($search) {
                $builder->where('plate_number', 'like', "%{$search}%")
                    ->orWhere('vehicle_type', 'like', "%{$search}%")
                    ->orWhere('brand', 'like', "%{$search}%");
            });
        }

        return view('pages.general-affair.vehicles.index', [
            'title' => 'Daftar Kendaraan',
            'vehicles' => $query->paginate(10)->withQueryString(),
            'search' => $request->string('search')->toString(),
        ]);
    }

    public function create()
    {
        return view('pages.general-affair.vehicles.create', [
            'title' => 'Input Kendaraan',
            'locations' => $this->locationOptions(),
            'drivers' => $this->driverOptions(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validatedData($request);

        if ($request->hasFile('stnk_document')) {
            $data['stnk_document_path'] = UploadHelper::store($request->file('stnk_document'), 'general-affair/vehicles/stnk');
        }

        if ($request->hasFile('kir_document')) {
            $data['kir_document_path'] = UploadHelper::store($request->file('kir_document'), 'general-affair/vehicles/kir');
        }

        GeneralAffairVehicle::create($data);

        return redirect()->route('general-affair.vehicles.index')->with('success', 'Data kendaraan berhasil dibuat.');
    }

    public function show(GeneralAffairVehicle $vehicle)
    {
        return view('pages.general-affair.vehicles.show', [
            'title' => $vehicle->plate_number,
            'vehicle' => $vehicle->load(['location', 'driver']),
        ]);
    }

    public function edit(GeneralAffairVehicle $vehicle)
    {
        return view('pages.general-affair.vehicles.edit', [
            'title' => 'Edit Kendaraan',
            'vehicle' => $vehicle,
            'locations' => $this->locationOptions(),
            'drivers' => $this->driverOptions(),
        ]);
    }

    public function update(Request $request, GeneralAffairVehicle $vehicle)
    {
        $data = $this->validatedData($request, $vehicle->id);

        if ($request->hasFile('stnk_document')) {
            UploadHelper::delete($vehicle->stnk_document_path);
            $data['stnk_document_path'] = UploadHelper::store($request->file('stnk_document'), 'general-affair/vehicles/stnk');
        }

        if ($request->hasFile('kir_document')) {
            UploadHelper::delete($vehicle->kir_document_path);
            $data['kir_document_path'] = UploadHelper::store($request->file('kir_document'), 'general-affair/vehicles/kir');
        }

        $vehicle->update($data);

        return redirect()->route('general-affair.vehicles.show', $vehicle)->with('success', 'Data kendaraan berhasil diperbarui.');
    }

    public function updateStnk(Request $request, GeneralAffairVehicle $vehicle)
    {
        $data = $request->validate([
            'tax_valid_until' => ['nullable', 'date'],
            'plate_valid_until' => ['nullable', 'date'],
            'stnk_document' => ['nullable', 'file', 'max:4096'],
        ]);

        if ($request->hasFile('stnk_document')) {
            UploadHelper::delete($vehicle->stnk_document_path);
            $data['stnk_document_path'] = UploadHelper::store($request->file('stnk_document'), 'general-affair/vehicles/stnk');
        }

        $vehicle->update($data);

        return back()->with('success', 'Masa berlaku STNK berhasil diperbarui.');
    }

    public function updateKir(Request $request, GeneralAffairVehicle $vehicle)
    {
        $data = $request->validate([
            'kir_valid_until' => ['nullable', 'date'],
            'kir_document' => ['nullable', 'file', 'max:4096'],
        ]);

        if ($request->hasFile('kir_document')) {
            UploadHelper::delete($vehicle->kir_document_path);
            $data['kir_document_path'] = UploadHelper::store($request->file('kir_document'), 'general-affair/vehicles/kir');
        }

        $vehicle->update($data);

        return back()->with('success', 'Masa berlaku KIR berhasil diperbarui.');
    }

    public function destroy(GeneralAffairVehicle $vehicle)
    {
        UploadHelper::delete($vehicle->stnk_document_path);
        UploadHelper::delete($vehicle->kir_document_path);
        $vehicle->delete();

        return redirect()->route('general-affair.vehicles.index')->with('success', 'Data kendaraan berhasil dihapus.');
    }

    private function validatedData(Request $request, ?int $vehicleId = null): array
    {
        return $request->validate([
            'plate_number' => [
                'required',
                'string',
                'max:30',
                Rule::unique('general_affair_vehicles', 'plate_number')->whereNull('deleted_at')->ignore($vehicleId),
            ],
            'vehicle_type' => ['nullable', 'string', 'max:100'],
            'brand' => ['nullable', 'string', 'max:100'],
            'manufacture_year' => ['nullable', 'integer', 'min:1900', 'max:'.((int) now()->format('Y') + 1)],
            'location_id' => ['nullable', 'exists:locations,id'],
            'driver_id' => ['nullable', 'exists:employees,id'],
            'ownership_status' => ['required', Rule::in(['asset'])],
            'tax_valid_until' => ['nullable', 'date'],
            'plate_valid_until' => ['nullable', 'date'],
            'stnk_document' => ['nullable', 'file', 'max:4096'],
            'kir_valid_until' => ['nullable', 'date'],
            'kir_document' => ['nullable', 'file', 'max:4096'],
            'notes' => ['nullable', 'string'],
        ]);
    }

    private function locationOptions()
    {
        return Location::query()
            ->orderBy('location_name')
            ->get(['id', 'location_name']);
    }

    private function driverOptions()
    {
        return Employee::query()
            ->with('employeePosition.position.subDepartment')
            ->where('status_karyawan', 'active')
            ->whereHas('employeePosition.position.subDepartment', function ($query) {
                $query->where('sub_department_name', 'like', '%driver%');
            })
            ->orderBy('nama_karyawan')
            ->get(['id', 'nama_karyawan', 'nik_karyawan']);
    }
}
