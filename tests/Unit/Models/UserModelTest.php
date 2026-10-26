<?php
/**
 * UserModel Micro-Unit Test
 * Tests UserModel query methods, governance guards, and user entity operations
 */

require_once __DIR__ . '/../../TestCase.php';
require_once __DIR__ . '/../../../app/Models/UserModel.php';

class UserModelTest extends TestCase {
    public function testGetAllWithProfilesReturnsArrayOfUsers(): void {
        $users = UserModel::getAllWithProfiles();
        $this->assertTrue(is_array($users));
        $this->assertGreaterThan(0, count($users));

        $first = $users[0];
        $this->assertArrayHasKey('id', $first);
        $this->assertArrayHasKey('username', $first);
        $this->assertArrayHasKey('role', $first);
        $this->assertArrayHasKey('full_name', $first);
    }

    public function testGetFacultyOnlyFiltersOutAdmins(): void {
        $faculty = UserModel::getFacultyOnly();
        $this->assertTrue(is_array($faculty));
        foreach ($faculty as $u) {
            $this->assertNotEquals('dean', $u['username'], "getFacultyOnly must not include dean");
        }
    }

    public function testFindByUsernameFindsDean(): void {
        $dean = UserModel::findByUsername('dean');
        $this->assertNotNull($dean, "Dean account must exist");
        $this->assertEquals('dean', $dean['username']);
        $this->assertEquals('dean', $dean['role']);
    }

    public function testFindByUsernameNonExistentReturnsNull(): void {
        $user = UserModel::findByUsername('definitely_non_existent_username_xyz');
        $this->assertNull($user);
    }

    public function testFindById(): void {
        $dean = UserModel::findByUsername('dean');
        $this->assertNotNull($dean);
        $found = UserModel::find((int)$dean['id']);
        $this->assertNotNull($found);
        $this->assertEquals($dean['id'], $found['id']);
        $this->assertEquals('dean', $found['username']);
    }

    public function testGetStatsReturnsValidSchema(): void {
        $stats = UserModel::getStats();
        $this->assertTrue(is_array($stats));
        $this->assertArrayHasKey('totalUsers', $stats);
        $this->assertArrayHasKey('activeUsers', $stats);
        $this->assertArrayHasKey('deanCount', $stats);
        $this->assertArrayHasKey('inactiveUsers', $stats);
        $this->assertGreaterThan(0, $stats['totalUsers']);
        $this->assertGreaterThan(0, $stats['deanCount']);
    }

    public function testGetFacultyLookupReturnsKeyValueMap(): void {
        $lookup = UserModel::getFacultyLookup();
        $this->assertTrue(is_array($lookup));
        $this->assertGreaterThan(0, count($lookup));
        foreach ($lookup as $id => $name) {
            $this->assertTrue(is_numeric($id));
            $this->assertTrue(is_string($name));
        }
    }

    public function testSelfDeactivationGuard(): void {
        $dean = UserModel::findByUsername('dean');
        $this->assertNotNull($dean);
        $res = UserModel::toggleStatus((int)$dean['id'], (int)$dean['id']);
        $this->assertFalse($res['success'], "Self deactivation must be blocked");
    }

    public function testSelfDeletionGuard(): void {
        $dean = UserModel::findByUsername('dean');
        $this->assertNotNull($dean);
        $res = UserModel::delete((int)$dean['id'], (int)$dean['id']);
        $this->assertFalse($res['success'], "Self deletion must be blocked");
    }
}
