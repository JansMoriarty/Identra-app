<?

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Classroom;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ClassroomController extends Controller
{
    public function index()
    {
        $classrooms = Classroom::latest()->get();
        return response()->json([
            'success' => true,
            'data'    => $classrooms
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            // 'location' => 'nullable|string'
        ]);

        // Generate QR Code unik: CLS-RANDOMSTRING
        $uniqueCode = 'CLS-' . strtoupper(Str::random(10));

        $classroom = Classroom::create([
            'name'     => $request->name,
            // 'location' => $request->location,
            'qr_code'  => $uniqueCode, 
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Kelas berhasil dibuat',
            'data'    => $classroom
        ], 201);
    }

    public function show(Classroom $classroom)
    {
        return response()->json([
            'success' => true,
            'data'    => $classroom
        ]);
    }

    public function destroy(Classroom $classroom)
    {
        $classroom->delete();
        return response()->json([
            'success' => true,
            'message' => 'Kelas berhasil dihapus'
        ]);
    }
}