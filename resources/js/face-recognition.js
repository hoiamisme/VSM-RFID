/**
 * Face Recognition Module
 * 
 * Module untuk handle face detection, recognition, dan verification
 * menggunakan face-api.js
 * 
 * @author VMS Development Team
 * @version 2.0
 */

import * as faceapi from 'face-api.js';

class FaceRecognition {
    constructor() {
        this.modelsLoaded = false;
        this.video = null;
        this.stream = null;
        this.canvas = null;
        this.detectionInterval = null;
        this.lastDetection = null;
    }

    /**
     * Load face-api.js models
     */
    async loadModels() {
        if (this.modelsLoaded) {
            console.log('Models already loaded');
            return true;
        }

        try {
            console.log('Loading face recognition models...');
            
            const MODEL_URL = '/models';
            
            await faceapi.nets.tinyFaceDetector.loadFromUri(MODEL_URL);
            await faceapi.nets.faceLandmark68Net.loadFromUri(MODEL_URL);
            await faceapi.nets.faceRecognitionNet.loadFromUri(MODEL_URL);
            
            this.modelsLoaded = true;
            console.log('✓ All models loaded successfully');
            return true;
        } catch (error) {
            console.error('Failed to load models:', error);
            throw new Error('Gagal memuat model face recognition. Pastikan file model tersedia.');
        }
    }

    /**
     * Start webcam stream
     */
    async startWebcam(videoElement) {
        try {
            this.video = videoElement;
            
            const constraints = {
                video: {
                    width: { ideal: 640 },
                    height: { ideal: 480 },
                    facingMode: 'user'
                },
                audio: false
            };

            this.stream = await navigator.mediaDevices.getUserMedia(constraints);
            this.video.srcObject = this.stream;
            
            return new Promise((resolve) => {
                this.video.onloadedmetadata = () => {
                    this.video.play();
                    console.log('✓ Webcam started');
                    resolve(true);
                };
            });
        } catch (error) {
            console.error('Failed to start webcam:', error);
            throw new Error('Gagal mengakses kamera. Pastikan kamera terhubung dan izin diberikan.');
        }
    }

    /**
     * Stop webcam stream
     */
    stopWebcam() {
        if (this.stream) {
            this.stream.getTracks().forEach(track => track.stop());
            this.stream = null;
        }
        if (this.video) {
            this.video.srcObject = null;
        }
        if (this.detectionInterval) {
            clearInterval(this.detectionInterval);
            this.detectionInterval = null;
        }
        console.log('✓ Webcam stopped');
    }

    /**
     * Detect single face from video
     */
    async detectFace(options = {}) {
        if (!this.modelsLoaded) {
            throw new Error('Models belum dimuat');
        }

        if (!this.video) {
            throw new Error('Video belum diinisialisasi');
        }

        try {
            const detectionOptions = new faceapi.TinyFaceDetectorOptions({
                inputSize: options.inputSize || 416,
                scoreThreshold: options.scoreThreshold || 0.5
            });

            const detection = await faceapi
                .detectSingleFace(this.video, detectionOptions)
                .withFaceLandmarks()
                .withFaceDescriptor();

            this.lastDetection = detection;
            return detection;
        } catch (error) {
            console.error('Face detection error:', error);
            return null;
        }
    }

    /**
     * Start continuous face detection with callback
     */
    startContinuousDetection(callback, interval = 500) {
        this.detectionInterval = setInterval(async () => {
            const detection = await this.detectFace();
            if (callback) {
                callback(detection);
            }
        }, interval);
    }

    /**
     * Draw detection box on canvas
     */
    drawDetection(canvas, detection) {
        if (!detection) return;

        const displaySize = {
            width: this.video.videoWidth,
            height: this.video.videoHeight
        };

        faceapi.matchDimensions(canvas, displaySize);
        
        const resizedDetections = faceapi.resizeResults(detection, displaySize);
        
        // Clear canvas
        const ctx = canvas.getContext('2d');
        ctx.clearRect(0, 0, canvas.width, canvas.height);
        
        // Draw box
        faceapi.draw.drawDetections(canvas, resizedDetections);
        faceapi.draw.drawFaceLandmarks(canvas, resizedDetections);
    }

    /**
     * Verify face against stored descriptor
     */
    async verifyFace(liveDescriptor, storedDescriptor) {
        if (!Array.isArray(liveDescriptor) || !Array.isArray(storedDescriptor)) {
            throw new Error('Descriptor harus berupa array');
        }

        const distance = faceapi.euclideanDistance(liveDescriptor, storedDescriptor);
        const similarity = 1 - distance;
        
        return {
            match: similarity >= 0.6,
            similarity: similarity,
            confidence: Math.round(similarity * 100),
            distance: distance
        };
    }

    /**
     * Get face descriptor from detection
     */
    getDescriptor(detection) {
        if (!detection || !detection.descriptor) {
            return null;
        }
        return Array.from(detection.descriptor);
    }

    /**
     * Check if webcam is supported
     */
    static isWebcamSupported() {
        return !!(navigator.mediaDevices && navigator.mediaDevices.getUserMedia);
    }

    /**
     * Request camera permission
     */
    static async requestCameraPermission() {
        try {
            const stream = await navigator.mediaDevices.getUserMedia({ video: true });
            stream.getTracks().forEach(track => track.stop());
            return true;
        } catch (error) {
            console.error('Camera permission denied:', error);
            return false;
        }
    }
}

// Export untuk digunakan di window scope
window.FaceRecognition = FaceRecognition;

export default FaceRecognition;
