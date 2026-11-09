const FaceKit = (() => {
    // 320 jauh lebih ringan dari 416 dan tetap akurat untuk wajah dekat kamera
    const opts = new faceapi.TinyFaceDetectorOptions({ inputSize: 320, scoreThreshold: 0.5 });
    const tunggu = (ms) => new Promise((r) => setTimeout(r, ms));
    let streamAktif = null;

    function pesanError(e) {
        const peta = {
            NotFoundError: 'Kamera tidak ditemukan. Pastikan webcam aktif/terpasang, lalu klik "Coba Kamera Lagi".',
            NotAllowedError: 'Izin kamera ditolak. Klik ikon kamera di kiri address bar lalu pilih Allow.',
            NotReadableError: 'Kamera sedang dipakai aplikasi lain atau macet. Tutup aplikasi lain lalu coba lagi.',
            OverconstrainedError: 'Pengaturan kamera tidak didukung. Coba lagi.',
            AbortError: 'Kamera gagal dimulai. Coba lagi.',
        };
        if (/timeout/i.test(e.message || '')) return 'Kamera terlalu lama merespons (timeout). Klik "Coba Kamera Lagi" atau pakai Upload Foto.';
        return peta[e.name] || e.message || 'Kamera bermasalah.';
    }

    async function muatModel(url) {
        try { await faceapi.tf.setBackend('webgl'); await faceapi.tf.ready(); } catch (_) { /* pakai backend bawaan */ }
        await Promise.all([
            faceapi.nets.tinyFaceDetector.loadFromUri(url),
            faceapi.nets.faceLandmark68Net.loadFromUri(url),
            faceapi.nets.faceRecognitionNet.loadFromUri(url),
        ]);
    }

    function matikanKamera(video) {
        if (streamAktif) { streamAktif.getTracks().forEach((t) => t.stop()); streamAktif = null; }
        if (video) video.srcObject = null;
    }

    // onPutus dipanggil bila kamera mati sendiri di tengah jalan (kabel longgar, dll.)
    async function nyalakanKamera(video, onPutus) {
        if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
            throw new Error('Kamera butuh localhost/HTTPS. Buka lewat http://127.0.0.1:8000');
        }
        matikanKamera(video);

        const daftar = [
            { video: { facingMode: 'user', width: { ideal: 640 }, height: { ideal: 480 }, frameRate: { ideal: 15 } }, audio: false },
            { video: true, audio: false }, // cadangan: paling longgar
        ];

        let stream = null, errTerakhir = null;
        for (const c of daftar) {
            try {
                stream = await Promise.race([
                    navigator.mediaDevices.getUserMedia(c),
                    new Promise((_, rej) => setTimeout(() => rej(new Error('Timeout starting video source')), 10000)),
                ]);
                break;
            } catch (e) { errTerakhir = e; await tunggu(400); }
        }
        if (!stream) { const err = new Error(pesanError(errTerakhir)); err.name = errTerakhir.name; throw err; }

        streamAktif = stream;
        stream.getVideoTracks().forEach((t) => { t.onended = () => { if (onPutus) onPutus(); }; });

        video.srcObject = stream;
        if (video.readyState < 1) await new Promise((r) => { video.onloadedmetadata = r; });
        await video.play();
        return stream;
    }

    function kameraHidup() {
        return !!streamAktif && streamAktif.getVideoTracks().some((t) => t.readyState === 'live');
    }

    // Kumpulkan N sampel wajah berkualitas baik
    async function ambilSampel(video, jumlah, batasMs, onProgress) {
        const hasil = [];
        const mulai = Date.now();
        while (hasil.length < jumlah && Date.now() - mulai < batasMs) {
            if (!kameraHidup()) break;
            try {
                const det = await faceapi.detectSingleFace(video, opts).withFaceLandmarks().withFaceDescriptor();
                if (det && det.detection.score >= 0.6 && det.detection.box.width >= video.videoWidth * 0.2) {
                    hasil.push({ descriptor: Array.from(det.descriptor), skor: det.detection.score });
                    if (onProgress) onProgress(hasil.length, jumlah);
                }
            } catch (_) { /* frame gagal, lanjut */ }
            await tunggu(200);
        }
        return hasil;
    }

    // Cadangan tanpa kamera: ambil descriptor dari file foto
    async function dariFile(file) {
        const bmp = await createImageBitmap(file);
        const skala = Math.min(1, 640 / Math.max(bmp.width, bmp.height));
        const c = document.createElement('canvas');
        c.width = Math.round(bmp.width * skala);
        c.height = Math.round(bmp.height * skala);
        c.getContext('2d').drawImage(bmp, 0, 0, c.width, c.height);

        const det = await faceapi.detectSingleFace(c, opts).withFaceLandmarks().withFaceDescriptor();
        if (!det || det.detection.score < 0.6) return null;

        const f = document.createElement('canvas');
        f.width = 320;
        f.height = Math.round(320 * c.height / c.width);
        f.getContext('2d').drawImage(c, 0, 0, f.width, f.height);
        return { descriptor: Array.from(det.descriptor), skor: det.detection.score, foto: f.toDataURL('image/jpeg', 0.7) };
    }

    // Foto apa adanya dari kamera (TIDAK dimirror)
    function snapshot(video, lebar = 320) {
        const c = document.createElement('canvas');
        c.width = lebar;
        c.height = Math.round(lebar * video.videoHeight / video.videoWidth);
        c.getContext('2d').drawImage(video, 0, 0, c.width, c.height);
        return c.toDataURL('image/jpeg', 0.7);
    }

    return { muatModel, nyalakanKamera, matikanKamera, kameraHidup, ambilSampel, dariFile, snapshot, tunggu, pesanError };
})();