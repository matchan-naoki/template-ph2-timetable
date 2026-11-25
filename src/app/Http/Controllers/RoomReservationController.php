<?php

namespace App\Http\Controllers;

use App\Models\Room;
use App\Models\Course;
use App\Models\RoomReservation;
use Illuminate\Http\Request;
use Illuminate\Database\QueryException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class RoomReservationController extends Controller
{
    public function index(Request $request)
    {
        $data = $request->validate([
            'date' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:today'],
            'period' => ['nullable', 'integer', 'between:1,6'],
        ]);

        $date = $data['date'] ?? now()->toDateString();
        $period = (int) ($data['period'] ?? 1);
        $day = Carbon::parse($date)->dayOfWeekIso;

        // 授業で使用されている教室
        $busyRooms = Course::where('day', $day)
            ->where('period', $period)
            ->whereNotNull('room')
            ->pluck('room');

        // 既に予約されている教室
        $reservedRooms = RoomReservation::whereDate('date', $date)
            ->where('period', $period)
            ->pluck('room_code');

        // 空き教室一覧（授業中・予約済みを除外）
        $rooms = Room::query()
            ->whereNotIn('code', $busyRooms)
            ->whereNotIn('code', $reservedRooms)
            ->orderBy('building')
            ->orderBy('code')
            ->get();

        // ログイン中のユーザーの予約一覧
        $myReservations = RoomReservation::with('room')
            ->where('user_id', $request->user()->id)
            ->orderBy('date')
            ->orderBy('period')
            ->get();

        return view('room_reservations.index', compact(
            'rooms',
            'date',
            'period',
            'myReservations'
        ));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'room_code' => ['required', 'string', 'exists:rooms,code'],
            'date' => ['required', 'date_format:Y-m-d', 'after_or_equal:today'],
            'period' => ['required', 'integer', 'between:1,6'],
        ]);

        $date = $data['date'];
        $period = (int) $data['period'];
        $roomCode = $data['room_code'];
        $day = Carbon::parse($date)->dayOfWeekIso;

        try {
            DB::transaction(function () use ($request, $date, $period, $roomCode, $day) {

                // ユーザー行をロックし、同じ人の同時予約を防止
                DB::table('users')
                    ->where('id', $request->user()->id)
                    ->lockForUpdate()
                    ->first();

                // 同じ日付・時限に既に予約していないか確認
                $hasMyReservation = RoomReservation::where('user_id', $request->user()->id)
                    ->whereDate('date', $date)
                    ->where('period', $period)
                    ->exists();

                if ($hasMyReservation) {
                    throw ValidationException::withMessages([
                        'room_code' => '同じ日付・時限には、1人1教室しか予約できません。',
                    ]);
                }

                // 教室をロック
                $room = Room::where('code', $roomCode)
                    ->lockForUpdate()
                    ->firstOrFail();

                // 授業が行われている教室は予約不可
                $hasClass = Course::where('room', $room->code)
                    ->where('day', $day)
                    ->where('period', $period)
                    ->exists();

                if ($hasClass) {
                    throw ValidationException::withMessages([
                        'room_code' => 'この時間帯は授業があるため予約できません。',
                    ]);
                }

                // 他のユーザーも含めて予約済みなら予約不可
                $alreadyReserved = RoomReservation::where('room_code', $roomCode)
                    ->whereDate('date', $date)
                    ->where('period', $period)
                    ->exists();

                if ($alreadyReserved) {
                    throw ValidationException::withMessages([
                        'room_code' => 'この教室は既に予約されています。',
                    ]);
                }

                // 予約を保存
                RoomReservation::create([
                    'user_id' => $request->user()->id,
                    'room_code' => $roomCode,
                    'date' => $date,
                    'period' => $period,
                ]);
            });

        } catch (QueryException $e) {

            if ($e->getCode() === '23505') {
                throw ValidationException::withMessages([
                    'room_code' => 'この時間帯は既に予約されています。',
                ]);
            }

            throw $e;
        }

        return redirect()
            ->route('room-reservations.index', [
                'date' => $date,
                'period' => $period,
            ])
            ->with('success', '教室の予約が完了しました。');
    }

    // 自分の予約だけ削除可能
    public function destroy(Request $request, RoomReservation $reservation)
    {
        if ($reservation->user_id !== $request->user()->id) {
            abort(403, '他のユーザーの予約は削除できません。');
        }

        $reservation->delete();

        return redirect()
            ->route('room-reservations.index')
            ->with('success', '教室の予約をキャンセルしました。');
    }
}
