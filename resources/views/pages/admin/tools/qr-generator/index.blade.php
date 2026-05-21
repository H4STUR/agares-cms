<x-app-layout>
  <h4 class="mb-3">QR Code Generator</h4>

  <div class="row g-4"
       x-data="{
           text: '',
           size: 256,
           ecLevel: 'M',
           darkColor: '#000000',
           lightColor: '#ffffff',
           generated: false,
           _qr: null,
           async generate() {
               if (!this.text.trim()) return;
               if (!this._qr) {
                   const mod = await import('https://cdn.jsdelivr.net/npm/qrcode@1.5.4/+esm');
                   this._qr = mod.default;
               }
               await this.$nextTick();
               await this._qr.toCanvas(this.$refs.canvas, this.text, {
                   width: this.size,
                   errorCorrectionLevel: this.ecLevel,
                   color: { dark: this.darkColor, light: this.lightColor },
                   margin: 2,
               });
               this.generated = true;
           },
           download() {
               const link = document.createElement('a');
               link.download = 'qrcode.png';
               link.href = this.$refs.canvas.toDataURL('image/png');
               link.click();
           }
       }">

    {{-- Settings --}}
    <div class="col-lg-5">
      <div class="card h-100">
        <div class="card-body d-flex flex-column gap-3">

          <div>
            <label class="form-label fw-semibold">Content</label>
            <textarea class="form-control" rows="4" x-model="text"
                      placeholder="Enter URL, text, email, phone…"></textarea>
          </div>

          <div class="row g-3">
            <div class="col-6">
              <label class="form-label fw-semibold">Size (px)</label>
              <select class="form-select" x-model.number="size">
                <option value="128">128</option>
                <option value="192">192</option>
                <option value="256" selected>256</option>
                <option value="384">384</option>
                <option value="512">512</option>
              </select>
            </div>

            <div class="col-6">
              <label class="form-label fw-semibold">Error correction</label>
              <select class="form-select" x-model="ecLevel">
                <option value="L">L — Low (7%)</option>
                <option value="M" selected>M — Medium (15%)</option>
                <option value="Q">Q — Quartile (25%)</option>
                <option value="H">H — High (30%)</option>
              </select>
            </div>

            <div class="col-6">
              <label class="form-label fw-semibold">Foreground</label>
              <div class="input-group">
                <input type="color" class="form-control form-control-color" x-model="darkColor" style="max-width:48px">
                <input type="text" class="form-control font-monospace" x-model="darkColor" maxlength="7">
              </div>
            </div>

            <div class="col-6">
              <label class="form-label fw-semibold">Background</label>
              <div class="input-group">
                <input type="color" class="form-control form-control-color" x-model="lightColor" style="max-width:48px">
                <input type="text" class="form-control font-monospace" x-model="lightColor" maxlength="7">
              </div>
            </div>
          </div>

          <button class="btn btn-primary mt-auto" @click="generate()" :disabled="!text.trim()">
            <i class="material-icons-outlined me-1" style="font-size:18px;vertical-align:middle">qr_code_2</i>
            Generate QR Code
          </button>

        </div>
      </div>
    </div>

    {{-- Preview --}}
    <div class="col-lg-7">
      <div class="card h-100">
        <div class="card-body d-flex flex-column align-items-center justify-content-center gap-3">

          <div x-show="!generated" class="text-center text-muted py-5">
            <i class="material-icons-outlined" style="font-size:64px;opacity:.25">qr_code</i>
            <p class="mb-0 mt-2">Your QR code will appear here</p>
          </div>

          <canvas x-ref="canvas" x-show="generated"
                  style="border-radius:8px;max-width:100%;image-rendering:pixelated"></canvas>

          <div class="d-flex gap-2" x-show="generated">
            <button class="btn btn-success" @click="download()">
              <i class="material-icons-outlined me-1" style="font-size:18px;vertical-align:middle">download</i>
              Download PNG
            </button>
            <button class="btn btn-outline-secondary" @click="generate()" title="Regenerate">
              <i class="material-icons-outlined" style="font-size:18px;vertical-align:middle">refresh</i>
            </button>
          </div>

        </div>
      </div>
    </div>

  </div>

</x-app-layout>
