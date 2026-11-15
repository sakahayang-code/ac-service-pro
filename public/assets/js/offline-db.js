// AC SERVICE PRO - Offline Storage & Sync Manager v1.0
const DB_NAME = 'ACServiceProDB';
const DB_VERSION = 1;

class OfflineDB {
    static async open() {
        return new Promise((resolve, reject) => {
            const request = indexedDB.open(DB_NAME, DB_VERSION);
            request.onupgradeneeded = (event) => {
                const db = event.target.result;
                if (!db.objectStoreNames.contains('cached_tasks')) {
                    db.createObjectStore('cached_tasks', { keyPath: 'id' });
                }
                if (!db.objectStoreNames.contains('sync_queue')) {
                    const queueStore = db.createObjectStore('sync_queue', { keyPath: 'id', autoIncrement: true });
                    queueStore.createIndex('status', 'status', { unique: false });
                }
            };
            request.onsuccess = () => resolve(request.result);
            request.onerror = () => reject(request.error);
        });
    }

    static async saveTask(task) {
        const db = await this.open();
        return new Promise((resolve, reject) => {
            const tx = db.transaction('cached_tasks', 'readwrite');
            tx.objectStore('cached_tasks').put(task);
            tx.oncomplete = () => resolve(true);
            tx.onerror = () => reject(tx.error);
        });
    }

    static async getTask(id) {
        const db = await this.open();
        return new Promise((resolve, reject) => {
            const tx = db.transaction('cached_tasks', 'readonly');
            const req = tx.objectStore('cached_tasks').get(Number(id));
            req.onsuccess = () => resolve(req.result);
            req.onerror = () => reject(req.error);
        });
    }

    static async getAllTasks() {
        const db = await this.open();
        return new Promise((resolve, reject) => {
            const tx = db.transaction('cached_tasks', 'readonly');
            const req = tx.objectStore('cached_tasks').getAll();
            req.onsuccess = () => resolve(req.result || []);
            req.onerror = () => reject(req.error);
        });
    }

    static async queueAction(action, payload) {
        const db = await this.open();
        return new Promise((resolve, reject) => {
            const tx = db.transaction('sync_queue', 'readwrite');
            const item = {
                action: action,
                payload: payload,
                status: 'pending',
                created_at: new Date().toISOString()
            };
            tx.objectStore('sync_queue').add(item);
            tx.oncomplete = () => {
                window.dispatchEvent(new CustomEvent('sync_queue_updated'));
                resolve(true);
            };
            tx.onerror = () => reject(tx.error);
        });
    }

    static async getPendingQueue() {
        const db = await this.open();
        return new Promise((resolve, reject) => {
            const tx = db.transaction('sync_queue', 'readonly');
            const req = tx.objectStore('sync_queue').getAll();
            req.onsuccess = () => resolve(req.result || []);
            req.onerror = () => reject(req.error);
        });
    }

    static async removeQueueItem(id) {
        const db = await this.open();
        return new Promise((resolve, reject) => {
            const tx = db.transaction('sync_queue', 'readwrite');
            tx.objectStore('sync_queue').delete(id);
            tx.oncomplete = () => resolve(true);
            tx.onerror = () => reject(tx.error);
        });
    }

    static async processSync() {
        if (!navigator.onLine) {
            console.log('Device still offline, sync postponed.');
            return;
        }

        const items = await this.getPendingQueue();
        if (!items || items.length === 0) {
            return;
        }

        console.log(`Processing ${items.length} offline sync items...`);
        let syncedCount = 0;

        const baseUrl = window.APP_BASE_URL || '';
        for (const item of items) {
            try {
                const res = await fetch(baseUrl + '/api/sync/push', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        action: item.action,
                        payload: item.payload,
                        queued_at: item.created_at
                    })
                });

                if (res.ok) {
                    await this.removeQueueItem(item.id);
                    syncedCount++;
                }
            } catch (err) {
                console.error('Sync item failed:', err);
                break;
            }
        }

        if (syncedCount > 0) {
            window.dispatchEvent(new CustomEvent('sync_completed', { detail: { count: syncedCount } }));
            if (window.showToast) {
                window.showToast(`${syncedCount} data offline berhasil disinkronkan ke server!`, 'success');
            }
        }
    }
}

// Auto-trigger sync when back online
window.addEventListener('online', () => {
    if (window.showToast) {
        window.showToast('Koneksi internet kembali aktif! Memulai sinkronisasi...', 'info');
    }
    OfflineDB.processSync();
});

window.addEventListener('offline', () => {
    if (window.showToast) {
        window.showToast('Koneksi internet terputus. Mode offline aktif.', 'warning');
    }
});

// Expose globally
window.OfflineDB = OfflineDB;
