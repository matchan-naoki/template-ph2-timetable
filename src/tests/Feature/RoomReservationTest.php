<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\Room;
use App\Models\RoomReservation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoomReservationTest extends TestCase
{
    use RefreshDatabase;

    // テスト用の空き教室を作成
    private function createRoom(string $code = 'TEST101'): Room
    {
        return Room::create([
            'code' => $code,
            'building' => 'テスト棟',
            'floor' => 1,
            'capacity' => 30,
        ]);
    }

    // 1. ログインしていない人は予約できない
    public function test_guest_cannot_reserve_room(): void
    {
        $this->createRoom();

        $response = $this->post('/room-reservations', [
            'room_code' => 'TEST101',
            'date' => '2026-12-01',
            'period' => 3,
        ]);

        $response->assertRedirect('/login');

        $this->assertDatabaseCount('room_reservations', 0);
    }

    // 2. ログイン中のユーザーは空き教室を予約できる
    public function test_user_can_reserve_available_room(): void
    {
        $user = User::factory()->create();
        $this->createRoom();

        $response = $this->actingAs($user)->post('/room-reservations', [
            'room_code' => 'TEST101',
            'date' => '2026-12-01',
            'period' => 3,
        ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('room_reservations', [
            'user_id' => $user->id,
            'room_code' => 'TEST101',
            'date' => '2026-12-01',
            'period' => 3,
        ]);
    }

    // 3. 同じユーザーは同じ日時に2回予約できない
    public function test_user_cannot_reserve_twice_at_same_time(): void
    {
        $user = User::factory()->create();

        $this->createRoom('TEST101');
        $this->createRoom('TEST102');

        RoomReservation::create([
            'user_id' => $user->id,
            'room_code' => 'TEST101',
            'date' => '2026-12-01',
            'period' => 3,
        ]);

        $response = $this->actingAs($user)->post('/room-reservations', [
            'room_code' => 'TEST102',
            'date' => '2026-12-01',
            'period' => 3,
        ]);

        $response->assertSessionHasErrors('room_code');

        $this->assertDatabaseCount('room_reservations', 1);
    }

    // 4. 授業中の教室は予約できない
    public function test_user_cannot_reserve_room_during_class(): void
    {
        $user = User::factory()->create();
        $this->createRoom();


$course = new Course();
$course->code = 'TEST-COURSE';
$course->name = 'テスト授業';
$course->teacher = 'テスト教員';
$course->day = 2;
$course->period = 3;
$course->room = 'TEST101';
$course->credits = 2;
$course->required = false;
$course->capacity = 30;
$course->save();


        // 2026年12月1日は火曜日
        $response = $this->actingAs($user)->post('/room-reservations', [
            'room_code' => 'TEST101',
            'date' => '2026-12-01',
            'period' => 3,
        ]);

        $response->assertSessionHasErrors('room_code');

        $this->assertDatabaseCount('room_reservations', 0);
    }

    // 5. 自分の予約はキャンセルできる
    public function test_user_can_cancel_own_reservation(): void
    {
        $user = User::factory()->create();
        $this->createRoom();

        $reservation = RoomReservation::create([
            'user_id' => $user->id,
            'room_code' => 'TEST101',
            'date' => '2026-12-01',
            'period' => 3,
        ]);

        $response = $this->actingAs($user)
            ->delete("/room-reservations/{$reservation->id}");

        $response->assertRedirect();

        $this->assertDatabaseMissing('room_reservations', [
            'id' => $reservation->id,
        ]);
    }

    // 6. 他人の予約はキャンセルできない
    public function test_user_cannot_cancel_others_reservation(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();

        $this->createRoom();

        $reservation = RoomReservation::create([
            'user_id' => $owner->id,
            'room_code' => 'TEST101',
            'date' => '2026-12-01',
            'period' => 3,
        ]);

        $response = $this->actingAs($otherUser)
            ->delete("/room-reservations/{$reservation->id}");

        $response->assertForbidden();

        $this->assertDatabaseHas('room_reservations', [
            'id' => $reservation->id,
            'user_id' => $owner->id,
        ]);
    }
}
