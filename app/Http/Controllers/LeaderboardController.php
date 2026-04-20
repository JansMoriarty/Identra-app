<?PHP

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class LeaderboardController extends Controller
{
    public function index()
    {
        $leaderboard = User::where('role', 'guru') // atau ->where('role', 'guru')
            ->withSum('pointLedgers as total_points', 'amount')
            ->orderByDesc('total_points')
            ->get();

        return view('pages.leaderboard.index', compact('leaderboard'));
    }
}
