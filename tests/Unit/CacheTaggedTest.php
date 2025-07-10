<?php

use GuepardoSys\Core\Cache\CacheTagged;
use GuepardoSys\Core\Cache\CacheManager;

describe('Cache Tagged', function () {
    beforeEach(function () {
        // Limpar cache antes de cada teste
        @array_map('unlink', glob(STORAGE_PATH . '/cache/data/*'));
        $this->manager = CacheManager::getInstance();
        $this->tagged = new CacheTagged($this->manager, ['users', 'profiles']);
    });

    it('can store and retrieve tagged cache', function () {
        $result = $this->tagged->put('user.1', ['name' => 'João'], 300);
        expect($result)->toBeTrue();

        $value = $this->tagged->get('user.1');
        expect($value)->toBe(['name' => 'João']);
    });

    it('can remember with tags', function () {
        $callCount = 0;

        $value1 = $this->tagged->remember('user.profile.1', function () use (&$callCount) {
            $callCount++;
            return ['id' => 1, 'name' => 'João Silva'];
        }, 300);

        $value2 = $this->tagged->remember('user.profile.1', function () use (&$callCount) {
            $callCount++;
            return ['id' => 1, 'name' => 'João Silva'];
        }, 300);

        expect($value1)->toBe(['id' => 1, 'name' => 'João Silva']);
        expect($value2)->toBe(['id' => 1, 'name' => 'João Silva']);
        expect($callCount)->toBe(1); // Closure executada apenas uma vez
    });

    it('can flush all entries with specific tags', function () {
        // Cache com tags 'users', 'profiles'
        $this->tagged->put('user.1', ['name' => 'João'], 300);
        $this->tagged->put('user.2', ['name' => 'Maria'], 300);

        // Cache com outras tags
        $otherTagged = new CacheTagged($this->manager, ['posts']);
        $otherTagged->put('post.1', ['title' => 'Post 1'], 300);

        // Verificar que estão armazenados
        expect($this->tagged->get('user.1'))->toBe(['name' => 'João']);
        expect($this->tagged->get('user.2'))->toBe(['name' => 'Maria']);
        expect($otherTagged->get('post.1'))->toBe(['title' => 'Post 1']);

        // Flush apenas tags 'users', 'profiles'
        $result = $this->tagged->flush();
        expect($result)->toBeTrue();

        // Cache com tags deve ter sido removido
        expect($this->tagged->get('user.1'))->toBeNull();
        expect($this->tagged->get('user.2'))->toBeNull();

        // Cache com outras tags deve permanecer
        expect($otherTagged->get('post.1'))->toBe(['title' => 'Post 1']);
    });

    it('can handle multiple tags', function () {
        $multiTagged = new CacheTagged($this->manager, ['users', 'admin', 'permissions']);

        $multiTagged->put('admin.user.1', ['role' => 'admin'], 300);
        $value = $multiTagged->get('admin.user.1');

        expect($value)->toBe(['role' => 'admin']);
    });

    it('adds keys to tag index correctly', function () {
        $this->tagged->put('user.profile.1', ['name' => 'João'], 300);
        $this->tagged->put('user.profile.2', ['name' => 'Maria'], 300);

        // Verificar se as chaves foram adicionadas ao índice de tags
        $userTagIndex = $this->manager->get('tag_index:users', []);
        $profileTagIndex = $this->manager->get('tag_index:profiles', []);

        expect($userTagIndex)->toContain('user.profile.1');
        expect($userTagIndex)->toContain('user.profile.2');
        expect($profileTagIndex)->toContain('user.profile.1');
        expect($profileTagIndex)->toContain('user.profile.2');
    });

    it('does not duplicate keys in tag index', function () {
        // Adicionar a mesma chave múltiplas vezes
        $this->tagged->put('duplicate.key', 'value1', 300);
        $this->tagged->put('duplicate.key', 'value2', 300);
        $this->tagged->put('duplicate.key', 'value3', 300);

        $userTagIndex = $this->manager->get('tag_index:users', []);

        // Deve aparecer apenas uma vez no índice
        $duplicateCount = array_count_values($userTagIndex)['duplicate.key'] ?? 0;
        expect($duplicateCount)->toBeLessThanOrEqual(1);
    });
});
