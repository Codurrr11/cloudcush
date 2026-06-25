// admin/assets/js/main.js

document.addEventListener("DOMContentLoaded", function () {

    // Initialize Lucide Icons globally on DOM load
    if (typeof lucide !== "undefined") {
        lucide.createIcons();
    }

    // ==========================================
    // SIDEBAR & LAYOUT
    // ==========================================
    const sidebarToggle = document.getElementById("sidebarToggle");
    const wrapper = document.getElementById("wrapper");
    const sidebar = document.getElementById("sidebar-wrapper");

    if (sidebarToggle && wrapper) {
        // Create backdrop dynamically
        let backdrop = document.getElementById("sidebar-backdrop");
        if (!backdrop) {
            backdrop = document.createElement("div");
            backdrop.id = "sidebar-backdrop";
            document.body.appendChild(backdrop);
        }

        const updateToggleIcon = () => {
            const currentIcon = sidebarToggle.querySelector("i, svg");
            if (currentIcon) {
                if (wrapper.classList.contains("collapsed")) {
                    currentIcon.outerHTML = '<i data-lucide="panel-left" class="text-secondary" style="width: 14px; height: 14px;"></i>';
                } else {
                    currentIcon.outerHTML = '<i data-lucide="panel-left-close" class="text-secondary" style="width: 14px; height: 14px;"></i>';
                }
                if (typeof lucide !== "undefined") {
                    lucide.createIcons();
                }
            }

            // Sync backdrop visibility
            if (window.innerWidth <= 992 && wrapper.classList.contains("sidebar-open")) {
                backdrop.style.display = "block";
                setTimeout(() => {
                    backdrop.style.opacity = "1";
                }, 10);
            } else {
                backdrop.style.opacity = "0";
                setTimeout(() => {
                    backdrop.style.display = "none";
                }, 250);
            }
        };

        // Initialize toggle button state on load
        if (window.innerWidth <= 992) {
            wrapper.classList.add("collapsed");
            sidebar.classList.add("collapsed");
        }
        updateToggleIcon();

        // Toggler click event
        sidebarToggle.addEventListener("click", function (e) {
            e.preventDefault();

            if (window.innerWidth <= 992) {
                wrapper.classList.toggle("sidebar-open");
                sidebar.classList.toggle("sidebar-open");
            } else {
                wrapper.classList.toggle("collapsed");
                sidebar.classList.toggle("collapsed");
            }
            updateToggleIcon();
        });

        // Backdrop click to close sidebar
        backdrop.addEventListener("click", function () {
            wrapper.classList.remove("sidebar-open");
            sidebar.classList.remove("sidebar-open");
            wrapper.classList.add("collapsed");
            sidebar.classList.add("collapsed");
            updateToggleIcon();
        });
    }

    // Close sidebar on mobile when resizing back to desktop
    window.addEventListener("resize", function () {
        if (window.innerWidth > 992 && wrapper && sidebar) {
            wrapper.classList.remove("sidebar-open");
            sidebar.classList.remove("sidebar-open");
            let backdrop = document.getElementById("sidebar-backdrop");
            if (backdrop) {
                backdrop.style.opacity = "0";
                backdrop.style.display = "none";
            }
        }
    });

    // ==========================================
    // DASHBOARD MODULE
    // ==========================================
    const ctx = document.getElementById('perfChart');
    if (ctx) {
        const chartCtx = ctx.getContext('2d');
        const gradient = chartCtx.createLinearGradient(0, 0, 0, 240);
        gradient.addColorStop(0, 'rgba(79, 70, 229, 0.2)');
        gradient.addColorStop(1, 'rgba(124, 58, 237, 0.001)');

        new Chart(ctx, {
            type: 'line',
            data: {
                labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
                datasets: [{
                    label: 'Revenue Growth',
                    data: [15, 28, 22, 45, 38, 59, 52, 68, 62, 79, 74, 88],
                    borderColor: '#4f46e5',
                    borderWidth: 2.5,
                    pointBackgroundColor: '#ffffff',
                    pointBorderColor: '#4f46e5',
                    pointBorderWidth: 2,
                    pointHoverRadius: 6,
                    pointHoverBorderWidth: 3,
                    tension: 0.4,
                    fill: true,
                    backgroundColor: gradient
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        mode: 'index',
                        intersect: false,
                        padding: 12,
                        backgroundColor: '#0f172a',
                        titleColor: '#ffffff',
                        titleFont: {
                            family: 'Plus Jakarta Sans',
                            size: 12,
                            weight: 'bold'
                        },
                        bodyColor: '#e2e8f0',
                        bodyFont: {
                            family: 'Inter',
                            size: 11
                        },
                        borderColor: 'rgba(255,255,255,0.08)',
                        borderWidth: 1,
                        displayColors: false,
                        cornerRadius: 8
                    }
                },
                scales: {
                    x: {
                        grid: {
                            display: false
                        },
                        ticks: {
                            color: '#94a3b8',
                            font: {
                                family: 'JetBrains Mono',
                                size: 9,
                                weight: '500'
                            }
                        },
                        border: {
                            display: false
                        }
                    },
                    y: {
                        grace: '15%',
                        grid: {
                            color: 'rgba(226, 232, 240, 0.4)',
                            drawTicks: false
                        },
                        ticks: {
                            color: '#94a3b8',
                            font: {
                                family: 'JetBrains Mono',
                                size: 9,
                                weight: '500'
                            }
                        },
                        border: {
                            display: false
                        }
                    }
                }
            }
        });
    }

    // ==========================================
    // PRODUCT MODULE
    // ==========================================

    /* 1. TinyMCE init (runs only if editor class exists on page) */
    if (typeof tinymce !== "undefined" && document.querySelector(".tinymce-editor")) {
        tinymce.init({
            selector: ".tinymce-editor",
            menubar: false,
            statusbar: true,
            resize: true,
            min_height: 280,
            plugins: "lists link image code table",
            toolbar:
                "undo redo | bold italic underline | bullist numlist | " +
                "link image | removeformat | code",
            skin: "oxide",
            content_css: "default",
            body_class: "tinymce-body",
            content_style:
                "body { font-family: 'Inter', Arial, sans-serif; " +
                "font-size: 14px; color: #0f172a; line-height: 1.65; margin: 12px; }",
            setup: function (editor) {
                editor.on("init", function () {
                    const wrap = editor.getContainer();
                    if (wrap) {
                        wrap.style.border      = "none";
                        wrap.style.borderRadius = "0";
                        wrap.style.boxShadow   = "none";
                    }
                });
            },
        });
    }

    const imageInput   = document.getElementById("productImageInput") || document.getElementById("blogImageInput") || document.getElementById("reviewMediaInput") || document.getElementById("aboutImageInput");
    const uploadZone   = document.getElementById("uploadZone");
    const previewWrap  = document.getElementById("uploadPreviewWrap");
    const previewImg   = document.getElementById("uploadPreviewImg");
    const previewVideo = document.getElementById("uploadPreviewVideo");
    const previewRmBtn = document.getElementById("previewRemoveBtn");
    const uploadBody   = document.getElementById("uploadZoneBody");

    function showImagePreview(file) {
        const reader = new FileReader();
        reader.onload = function (e) {
            if (file.type.startsWith("video/")) {
                if (previewImg) previewImg.style.display = "none";
                if (previewVideo) {
                    previewVideo.src = e.target.result;
                    previewVideo.style.display = "block";
                }
            } else {
                if (previewVideo) previewVideo.style.display = "none";
                if (previewImg) {
                    previewImg.src = e.target.result;
                    previewImg.style.display = "block";
                }
            }
            if (previewWrap) previewWrap.style.display = "block";
            if (uploadBody) uploadBody.style.display = "none";
        };
        reader.readAsDataURL(file);
    }

    if (imageInput && uploadZone) {
        imageInput.addEventListener("change", function () {
            if (this.files[0]) showImagePreview(this.files[0]);
        });

        // Drag & drop
        ["dragenter", "dragover"].forEach(evt =>
            uploadZone.addEventListener(evt, e => {
                e.preventDefault();
                uploadZone.classList.add("dragover");
            })
        );
        ["dragleave", "drop"].forEach(evt =>
            uploadZone.addEventListener(evt, e => {
                e.preventDefault();
                uploadZone.classList.remove("dragover");
            })
        );
        uploadZone.addEventListener("drop", function (e) {
            const file = e.dataTransfer?.files[0];
            if (!file) return;
            const dt = new DataTransfer();
            dt.items.add(file);
            imageInput.files = dt.files;
            showImagePreview(file);
        });

        if (previewRmBtn) {
            previewRmBtn.addEventListener("click", function (e) {
                e.preventDefault();
                e.stopPropagation();
                imageInput.value          = "";
                if (previewImg) { previewImg.src = ""; previewImg.style.display = "block"; }
                if (previewVideo) { previewVideo.src = ""; previewVideo.style.display = "none"; }
                if (previewWrap) previewWrap.style.display = "none";
                if (uploadBody) uploadBody.style.display = "block";
            });
        }
    }


    /* 3. Gallery multi-image upload preview */
    const galleryInput = document.getElementById("galleryInput");
    const galleryContainer = document.getElementById("galleryContainer");

    if (galleryInput && galleryContainer) {
        galleryInput.addEventListener("change", function () {
            Array.from(this.files).forEach(function (file) {
                const reader = new FileReader();
                reader.onload = function (e) {
                    const wrap = document.createElement("div");
                    wrap.className = "gallery-image-item card p-2 bg-light";
                    wrap.style.width = "140px";
                    wrap.style.border = "1px solid var(--border)";
                    wrap.style.borderRadius = "12px";
                    wrap.fileObject = file; // Store reference to original File
                    
                    wrap.innerHTML =
                        `<img src="${e.target.result}" alt="Gallery Image" class="img-thumbnail mb-2" style="height: 100px; width: 100%; object-fit: cover; border-radius: 8px;">` +
                        `<input type="hidden" name="gallery_images_order[]" value="new:0">` +
                        `<div class="d-flex justify-content-between align-items-center gap-1">` +
                            `<button type="button" class="btn btn-sm btn-outline-secondary move-prev-gal px-2 py-1" title="Move Left">` +
                                `<i data-lucide="arrow-left" style="width:12px;height:12px;"></i>` +
                            `</button>` +
                            `<button type="button" class="btn btn-sm btn-outline-danger remove-gal-img-btn px-2 py-1" title="Remove">` +
                                `<i data-lucide="trash-2" style="width:12px;height:12px;"></i>` +
                            `</button>` +
                            `<button type="button" class="btn btn-sm btn-outline-secondary move-next-gal px-2 py-1" title="Move Right">` +
                                `<i data-lucide="arrow-right" style="width:12px;height:12px;"></i>` +
                            `</button>` +
                        `</div>`;

                    galleryContainer.appendChild(wrap);
                    
                    if (typeof lucide !== "undefined") {
                        lucide.createIcons();
                    }
                    
                    rebuildGalleryImages();
                };
                reader.readAsDataURL(file);
            });
            this.value = ""; // Clear to allow selecting the same file
        });
    }

    /* 3b. Detail description images upload preview inside unified container */
    const detailImagesInput = document.getElementById("detailImagesInput");
    const detailImagesContainer = document.getElementById("detailImagesContainer");

    if (detailImagesInput && detailImagesContainer) {
        detailImagesInput.addEventListener("change", function () {
            Array.from(this.files).forEach(function (file) {
                const reader = new FileReader();
                reader.onload = function (e) {
                    const wrap = document.createElement("div");
                    wrap.className = "detail-image-item card p-2 bg-light";
                    wrap.style.width = "140px";
                    wrap.style.border = "1px solid var(--border)";
                    wrap.style.borderRadius = "12px";
                    wrap.fileObject = file; // Store reference to original File
                    
                    wrap.innerHTML =
                        `<img src="${e.target.result}" alt="Detail Image" class="img-thumbnail mb-2" style="height: 100px; width: 100%; object-fit: cover; border-radius: 8px;">` +
                        `<input type="hidden" name="detail_images_order[]" value="new:0">` +
                        `<div class="d-flex justify-content-between align-items-center gap-1">` +
                            `<button type="button" class="btn btn-sm btn-outline-secondary move-prev-btn px-2 py-1" title="Move Left">` +
                                `<i data-lucide="arrow-left" style="width:12px;height:12px;"></i>` +
                            `</button>` +
                            `<button type="button" class="btn btn-sm btn-outline-danger remove-det-img-btn px-2 py-1" title="Remove">` +
                                `<i data-lucide="trash-2" style="width:12px;height:12px;"></i>` +
                            `</button>` +
                            `<button type="button" class="btn btn-sm btn-outline-secondary move-next-btn px-2 py-1" title="Move Right">` +
                                `<i data-lucide="arrow-right" style="width:12px;height:12px;"></i>` +
                            `</button>` +
                        `</div>`;

                    detailImagesContainer.appendChild(wrap);
                    
                    if (typeof lucide !== "undefined") {
                        lucide.createIcons();
                    }
                    
                    rebuildDetailImages();
                };
                reader.readAsDataURL(file);
            });
            this.value = ""; // Clear to allow selecting the same file
        });
    }

    /* 4. Variants repeater */
    const addVariantBtn   = document.getElementById("addVariantBtn");
    const variantsWrapper = document.getElementById("variantsWrapper");

    function bindRemoveVariant(btn) {
        btn.addEventListener("click", function () {
            btn.closest(".variant-row").remove();
        });
    }

    if (addVariantBtn && variantsWrapper) {
        variantsWrapper.querySelectorAll(".btn-remove-variant").forEach(bindRemoveVariant);

        addVariantBtn.addEventListener("click", function () {
            const row = document.createElement("div");
            row.className = "variant-row";
            row.innerHTML =
                `<div><input type="text" name="variant_name[]" class="form-control-premium" placeholder="e.g. Size"></div>` +
                `<div><input type="text" name="variant_value[]" class="form-control-premium" placeholder="e.g. Medium (M)"></div>` +
                `<div><input type="number" name="variant_price[]" class="form-control-premium" placeholder="±₹0" step="0.01"></div>` +
                `<div><input type="number" name="variant_stock[]" class="form-control-premium" placeholder="0" min="0"></div>` +
                `<button type="button" class="btn-remove-variant" title="Remove variant">` +
                    `<i data-lucide="x" style="width:14px;height:14px;"></i>` +
                `</button>`;
            bindRemoveVariant(row.querySelector(".btn-remove-variant"));
            variantsWrapper.appendChild(row);
            if (typeof lucide !== "undefined") lucide.createIcons();
            row.querySelector("input")?.focus();
        });
    }

    function bindDeleteButtons() {
        document.querySelectorAll(".btn-delete-product, .btn-delete-blog, .btn-delete-review, .btn-delete-faq, .btn-delete-feature, .btn-delete-timeline, .btn-delete-metric, .btn-delete-layer").forEach(function (btn) {
            if (btn.dataset.deletebound) return;
            btn.dataset.deletebound = "1";

            btn.addEventListener("click", function (e) {
                e.preventDefault();
                const itemId      = this.dataset.id;
                const isBlog      = this.classList.contains("btn-delete-blog");
                const isReview    = this.classList.contains("btn-delete-review");
                const isFaq       = this.classList.contains("btn-delete-faq");
                const isFeature   = this.classList.contains("btn-delete-feature");
                const isTimeline  = this.classList.contains("btn-delete-timeline");
                const isMetric    = this.classList.contains("btn-delete-metric");
                const isLayer     = this.classList.contains("btn-delete-layer");
                
                const itemName    = this.dataset.name || (
                    isBlog ? "this article" : 
                    isReview ? "this review" : 
                    isFaq ? "this FAQ" : 
                    isFeature ? "this feature" : 
                    isTimeline ? "this milestone" : 
                    isMetric ? "this metric" : 
                    isLayer ? "this layer" : 
                    "this product"
                );
                
                let handlerUrlId = "deleteHandlerUrl";
                if (isBlog) handlerUrlId = "deleteBlogHandlerUrl";
                else if (isReview) handlerUrlId = "deleteReviewHandlerUrl";
                else if (isFaq) handlerUrlId = "deleteFaqHandlerUrl";
                else if (isFeature) handlerUrlId = "deleteFeatureHandlerUrl";
                else if (isTimeline) handlerUrlId = "deleteTimelineHandlerUrl";
                else if (isMetric) handlerUrlId = "deleteMetricHandlerUrl";
                else if (isLayer) handlerUrlId = "deleteLayerHandlerUrl";
                
                const handlerUrl  = document.getElementById(handlerUrlId)?.value || "";
                
                let titleText = "Delete Product?";
                if (isBlog) titleText = "Delete Article?";
                else if (isReview) titleText = "Delete Review?";
                else if (isFaq) titleText = "Delete FAQ?";
                else if (isFeature) titleText = "Delete Feature?";
                else if (isTimeline) titleText = "Delete Timeline Step?";
                else if (isMetric) titleText = "Delete Metric Card?";
                else if (isLayer) titleText = "Delete Visual Layer?";

                Swal.fire({
                    title: titleText,
                    html: `<p style="font-size:0.88rem;color:#64748b;margin:0 0 0.5rem;">You are about to permanently delete</p>` +
                          `<p style="font-size:0.9rem;font-weight:700;color:#0f172a;margin:0;">${itemName}</p>` +
                          `<p style="font-size:0.78rem;color:#94a3b8;margin:0.65rem 0 0;">This action cannot be undone.</p>`,
                    icon: "warning",
                    showCancelButton: true,
                    confirmButtonText: "Yes, delete it",
                    cancelButtonText: "Cancel",
                    confirmButtonColor: "#dc2626",
                    cancelButtonColor: "#e2e8f0",
                    reverseButtons: true,
                    customClass: {
                        popup:         "swal2-premium-popup",
                        confirmButton: "swal2-confirm-danger",
                        cancelButton:  "swal2-cancel-secondary",
                    },
                }).then(function (result) {
                    if (result.isConfirmed) {
                        const form  = document.createElement("form");
                        form.method = "POST";
                        form.action = handlerUrl;
                        const input = document.createElement("input");
                        input.type  = "hidden";
                        input.name  = "id";
                        input.value = itemId;
                        form.appendChild(input);
                        document.body.appendChild(form);
                        form.submit();
                    }
                });
            });
        });
    }
    bindDeleteButtons();


    /* 6. Slug: auto-generate + editable toggle */
    function toSlug(str) {
        return str
            .toLowerCase()
            .trim()
            .replace(/[^a-z0-9\s\-]/g, "")
            .replace(/[\s\-]+/g, "-")
            .replace(/^-+|-+$/g, "");
    }

    function setupSlugListener(titleIn, slugIn, btnEdit) {
        if (!titleIn || !slugIn) return;
        titleIn.addEventListener("input", function () {
            if (slugIn.dataset.userEdited === "true") return;
            slugIn.value = toSlug(this.value);
        });

        slugIn.addEventListener("input", function () {
            if (!slugIn.hasAttribute("readonly")) {
                slugIn.dataset.userEdited = "true";
            }
        });

        if (btnEdit) {
            btnEdit.addEventListener("click", function () {
                const isReadonly = slugIn.hasAttribute("readonly");
                if (isReadonly) {
                    slugIn.removeAttribute("readonly");
                    slugIn.dataset.userEdited = "true";
                    slugIn.focus();
                    slugIn.setSelectionRange(slugIn.value.length, slugIn.value.length);
                    btnEdit.title = "Lock slug";
                    btnEdit.style.color = "#4f46e5";
                } else {
                    slugIn.value = toSlug(slugIn.value);
                    slugIn.setAttribute("readonly", "");
                    btnEdit.title = "Edit slug manually";
                    btnEdit.style.color = "";
                }
                if (typeof lucide !== "undefined") lucide.createIcons();
            });
        }
    }

    setupSlugListener(document.getElementById("productTitle"), document.getElementById("productSlug"), document.getElementById("slugEditBtn"));
    setupSlugListener(document.getElementById("blogTitle"), document.getElementById("blogSlug"), document.getElementById("blogSlugEditBtn"));

    /* 7. Category custom input toggle */
    function setupCategoryToggle(selectEl, customWrapEl) {
        if (!selectEl || !customWrapEl) return;
        function toggleCustomCat() {
            const show = selectEl.value === "__custom__";
            customWrapEl.style.display = show ? "block" : "none";
            if (show) customWrapEl.querySelector("input")?.focus();
        }
        selectEl.addEventListener("change", toggleCustomCat);
        toggleCustomCat();
    }
    setupCategoryToggle(document.getElementById("productCategory"), document.getElementById("customCategoryWrap"));
    setupCategoryToggle(document.getElementById("blogCategory"), document.getElementById("blogCustomCategoryWrap"));

    /* 8. Tag pills live preview */
    const tagsInput    = document.getElementById("productTags");
    const tagPillsDisp = document.getElementById("tagPillsDisplay");

    function renderTagPills(val) {
        if (!tagPillsDisp) return;
        const tags = val.split(",").map(t => t.trim()).filter(Boolean);
        tagPillsDisp.innerHTML = tags
            .map(t => `<span class="tag-pill-item">${escapeHtml(t)}</span>`)
            .join("");
    }

    function escapeHtml(str) {
        return str.replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;");
    }

    if (tagsInput) {
        tagsInput.addEventListener("input", () => renderTagPills(tagsInput.value));
        renderTagPills(tagsInput.value);
    }

    /* 9. Detail view gallery switcher (smooth fade) */
    const detailMainImg = document.getElementById("detailMainImg");

    if (detailMainImg) {
        detailMainImg.style.transition = "opacity 0.15s ease";

        document.querySelectorAll(".detail-gallery-thumb").forEach(function (thumb) {
            thumb.addEventListener("click", function () {
                const newSrc = this.dataset.src;
                if (!newSrc || detailMainImg.src === newSrc) return;

                detailMainImg.style.opacity = "0";
                setTimeout(function () {
                    detailMainImg.src = newSrc;
                    detailMainImg.style.opacity = "1";
                }, 150);

                document.querySelectorAll(".detail-gallery-thumb").forEach(t => t.classList.remove("active"));
                this.classList.add("active");
            });
        });
    }

    /* 10. Filter selects: auto-submit on change */
    document.querySelectorAll(".filter-autosubmit").forEach(function (el) {
        el.addEventListener("change", function () {
            this.closest("form")?.submit();
        });
    });

    /* 11. Ensure TinyMCE content saved before form submit */
    document.querySelectorAll("form").forEach(function (form) {
        form.addEventListener("submit", function () {
            if (typeof tinymce !== "undefined") {
                tinymce.triggerSave();
            }
        });
    });

    /* 12. Status quick-toggle */
    document.querySelectorAll(".status-quick-toggle").forEach(function (sel) {
        sel.addEventListener("change", function () {
            const productId = this.dataset.id;
            const newStatus = this.value;
            const handler   = document.getElementById("statusHandlerUrl")?.value || "";
            if (!productId || !handler) return;

            const form      = document.createElement("form");
            form.method     = "POST";
            form.action     = handler;
            const fId       = document.createElement("input");
            fId.type        = "hidden";
            fId.name        = "id";
            fId.value       = productId;
            const fStatus   = document.createElement("input");
            fStatus.type    = "hidden";
            fStatus.name    = "status";
            fStatus.value   = newStatus;
            form.appendChild(fId);
            form.appendChild(fStatus);
            document.body.appendChild(form);
            form.submit();
        });
    });

    // Rebuild the file input's files array and update the indices of new files based on current DOM order
    function rebuildDetailImages() {
        const container = document.getElementById("detailImagesContainer");
        const fileInput = document.getElementById("detailImagesInput");
        if (!container || !fileInput) return;

        const items = container.querySelectorAll(".detail-image-item");
        const dt = new DataTransfer();
        let newIndex = 0;

        items.forEach(function (item) {
            const isNew = item.fileObject !== undefined;
            const hiddenInput = item.querySelector("input[type='hidden']");
            if (isNew) {
                dt.items.add(item.fileObject);
                if (hiddenInput) {
                    hiddenInput.value = "new:" + newIndex;
                }
                newIndex++;
            }
        });

        fileInput.files = dt.files;
    }

    // 13. Reorder Description Images Click Handler
    if (detailImagesContainer) {
        detailImagesContainer.addEventListener("click", function (e) {
            const btn = e.target.closest("button");
            if (!btn) return;
            
            const item = btn.closest(".detail-image-item");
            if (!item) return;

            e.preventDefault();
            e.stopPropagation();

            if (btn.classList.contains("move-prev-btn")) {
                const prev = item.previousElementSibling;
                if (prev) {
                    detailImagesContainer.insertBefore(item, prev);
                }
            } else if (btn.classList.contains("move-next-btn")) {
                const next = item.nextElementSibling;
                if (next) {
                    detailImagesContainer.insertBefore(next, item);
                }
            } else if (btn.classList.contains("remove-det-img-btn")) {
                item.remove();
            }

            rebuildDetailImages();
        });
    }

    // Rebuild the file input's files array and update the indices of new files based on current DOM order
    function rebuildGalleryImages() {
        const container = document.getElementById("galleryContainer");
        const fileInput = document.getElementById("galleryInput");
        if (!container || !fileInput) return;

        const items = container.querySelectorAll(".gallery-image-item");
        const dt = new DataTransfer();
        let newIndex = 0;

        items.forEach(function (item) {
            const isNew = item.fileObject !== undefined;
            const hiddenInput = item.querySelector("input[type='hidden']");
            if (isNew) {
                dt.items.add(item.fileObject);
                if (hiddenInput) {
                    hiddenInput.value = "new:" + newIndex;
                }
                newIndex++;
            }
        });

        fileInput.files = dt.files;
    }

    // 14. Reorder Gallery Images Click Handler
    if (galleryContainer) {
        galleryContainer.addEventListener("click", function (e) {
            const btn = e.target.closest("button");
            if (!btn) return;
            
            const item = btn.closest(".gallery-image-item");
            if (!item) return;

            e.preventDefault();
            e.stopPropagation();

            if (btn.classList.contains("move-prev-gal")) {
                const prev = item.previousElementSibling;
                if (prev) {
                    galleryContainer.insertBefore(item, prev);
                }
            } else if (btn.classList.contains("move-next-gal")) {
                const next = item.nextElementSibling;
                if (next) {
                    galleryContainer.insertBefore(next, item);
                }
            } else if (btn.classList.contains("remove-gal-img-btn")) {
                item.remove();
            }

            rebuildGalleryImages();
        });
    }

    // Export function globally so it can be called if needed
    window.rebuildDetailImages = rebuildDetailImages;
    window.rebuildGalleryImages = rebuildGalleryImages;

});
