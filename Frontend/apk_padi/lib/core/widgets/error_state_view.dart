import 'package:flutter/material.dart';

/// Jenis kendala teknis dan non-teknis pada aplikasi P.A.D.I.
enum AppErrorType {
  /// Perangkat tidak terhubung ke jaringan internet (Wi-Fi / Data Seluler mati)
  noInternet,

  /// Gangguan server, 500 Internal Server Error, atau crash database
  technicalError,

  /// Waktu tunggu server habis (Gateway Timeout / 504)
  serverTimeout,

  /// Pemeliharaan sistem terjadwal (Scheduled Maintenance)
  maintenance,

  /// Data, lahan, atau halaman tidak ditemukan (404 / Empty Data)
  notFound,

  /// Akses ditolak atau memerlukan verifikasi identitas (401 / 403)
  accessDenied,
}

class ErrorStateConfig {
  const ErrorStateConfig({
    required this.title,
    required this.message,
    required this.primaryButtonText,
    required this.icon,
    required this.iconColor,
    required this.iconBgColor,
    this.secondaryButtonText,
    this.badgeText,
    this.tips,
  });

  final String title;
  final String message;
  final String primaryButtonText;
  final String? secondaryButtonText;
  final IconData icon;
  final Color iconColor;
  final Color iconBgColor;
  final String? badgeText;
  final List<String>? tips;

  static ErrorStateConfig fromType(AppErrorType type, {String? customTitle, String? customMessage}) {
    switch (type) {
      case AppErrorType.noInternet:
        return ErrorStateConfig(
          title: customTitle ?? 'Koneksi Internet Terputus',
          message: customMessage ??
              'Perangkat Anda tidak terhubung ke internet. Periksa koneksi data seluler atau jaringan Wi-Fi Anda untuk melanjutkan.',
          primaryButtonText: 'Coba Hubungkan Kembali',
          secondaryButtonText: 'Buka Pengaturan',
          icon: Icons.wifi_off_rounded,
          iconColor: const Color(0xFF0284C7),
          iconBgColor: const Color(0xFFE0F2FE),
          badgeText: 'Status: Offline',
          tips: const [
            'Pastikan Mode Pesawat (Airplane Mode) tidak aktif.',
            'Periksa kestabilan kuota data atau sinyal seluler.',
            'Coba matikan dan nyalakan kembali Wi-Fi Anda.',
          ],
        );

      case AppErrorType.technicalError:
        return ErrorStateConfig(
          title: customTitle ?? 'Kendala Teknis Sementara',
          message: customMessage ??
              'Layanan server P.A.D.I. sedang mengalami gangguan sementara. Data Anda tetap aman dan sistem sedang kami pulihkan.',
          primaryButtonText: 'Muat Ulang Halaman',
          secondaryButtonText: 'Kembali ke Beranda',
          icon: Icons.dns_rounded,
          iconColor: const Color(0xFFDC2626),
          iconBgColor: const Color(0xFFFEF2F2),
          badgeText: 'Kode: ERR_SERVER_500',
          tips: const [
            'Gangguan ini biasanya bersifat sementara (1-2 menit).',
            'Silakan muat ulang halaman atau coba beberapa saat lagi.',
          ],
        );

      case AppErrorType.serverTimeout:
        return ErrorStateConfig(
          title: customTitle ?? 'Waktu Respon Habis',
          message: customMessage ??
              'Server membutuhkan waktu terlalu lama untuk merespons permintaan Anda. Kemungkinan lalu lintas jaringan sedang padat.',
          primaryButtonText: 'Coba Lagi Sekarang',
          secondaryButtonText: 'Kembali',
          icon: Icons.hourglass_empty_rounded,
          iconColor: const Color(0xFFD97706),
          iconBgColor: const Color(0xFFFEF3C7),
          badgeText: 'Kode: ERR_TIMEOUT_504',
          tips: const [
            'Periksa kecepatan koneksi internet Anda.',
            'Tunggu beberapa detik sebelum menekan tombol coba lagi.',
          ],
        );

      case AppErrorType.maintenance:
        return ErrorStateConfig(
          title: customTitle ?? 'Sistem Dalam Pemeliharaan',
          message: customMessage ??
              'Kami sedang meningkatkan performa dan fitur AI cerdas P.A.D.I. Layanan akan kembali beroperasi dalam waktu dekat.',
          primaryButtonText: 'Periksa Status Pembaruan',
          secondaryButtonText: 'Kembali Nanti',
          icon: Icons.engineering_rounded,
          iconColor: const Color(0xFF4F46E5),
          iconBgColor: const Color(0xFFEEF2FF),
          badgeText: 'Status: Peningkatan Sistem',
          tips: const [
            'Pemeliharaan rutin untuk stabilitas server.',
            'Seluruh data lahan dan riwayat scan Anda tersimpan aman.',
          ],
        );

      case AppErrorType.notFound:
        return ErrorStateConfig(
          title: customTitle ?? 'Data Tidak Ditemukan',
          message: customMessage ??
              'Informasi lahan, diagnosis tanaman, atau halaman yang Anda cari tidak tersedia atau telah dihapus.',
          primaryButtonText: 'Kembali ke Beranda',
          icon: Icons.search_off_rounded,
          iconColor: const Color(0xFF64748B),
          iconBgColor: const Color(0xFFF1F5F9),
          badgeText: 'Status: 404 Tidak Ditemukan',
        );

      case AppErrorType.accessDenied:
        return ErrorStateConfig(
          title: customTitle ?? 'Akses Memerlukan Verifikasi',
          message: customMessage ??
              'Sesi masuk Anda telah berakhir atau akun Anda memerlukan otorisasi lanjutan untuk membuka fitur ini.',
          primaryButtonText: 'Masuk Ulang',
          secondaryButtonText: 'Kembali',
          icon: Icons.lock_person_rounded,
          iconColor: const Color(0xFF059669),
          iconBgColor: const Color(0xFFECFDF5),
          badgeText: 'Status: 401 Diperlukan Autentikasi',
        );
    }
  }
}

/// Tampilan Clean & Human-Crafted untuk menangani kendala teknis dan non-teknis.
class ErrorStateView extends StatefulWidget {
  const ErrorStateView({
    super.key,
    required this.type,
    this.customTitle,
    this.customMessage,
    this.onRetry,
    this.onSecondaryAction,
    this.isCompact = false,
  });

  final AppErrorType type;
  final String? customTitle;
  final String? customMessage;
  final Future<void> Function()? onRetry;
  final VoidCallback? onSecondaryAction;
  final bool isCompact;

  @override
  State<ErrorStateView> createState() => _ErrorStateViewState();
}

class _ErrorStateViewState extends State<ErrorStateView> {
  bool _isLoading = false;

  Future<void> _handleRetry() async {
    if (widget.onRetry == null || _isLoading) return;

    setState(() => _isLoading = true);
    try {
      await widget.onRetry!();
    } finally {
      if (mounted) {
        setState(() => _isLoading = false);
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    final config = ErrorStateConfig.fromType(
      widget.type,
      customTitle: widget.customTitle,
      customMessage: widget.customMessage,
    );

    return Center(
      child: ConstrainedBox(
        constraints: const BoxConstraints(maxWidth: 440),
        child: Padding(
          padding: EdgeInsets.symmetric(
            horizontal: widget.isCompact ? 16 : 24,
            vertical: widget.isCompact ? 16 : 28,
          ),
          child: Column(
            mainAxisSize: MainAxisSize.min,
            mainAxisAlignment: MainAxisAlignment.center,
            crossAxisAlignment: CrossAxisAlignment.center,
            children: [
              // 1. Icon Container
              Container(
                width: widget.isCompact ? 56 : 72,
                height: widget.isCompact ? 56 : 72,
                decoration: BoxDecoration(
                  color: config.iconBgColor,
                  borderRadius: BorderRadius.circular(widget.isCompact ? 16 : 20),
                ),
                child: Icon(
                  config.icon,
                  size: widget.isCompact ? 28 : 36,
                  color: config.iconColor,
                ),
              ),

              const SizedBox(height: 16),

              // 2. Badge Status (Optional)
              if (config.badgeText != null) ...[
                Container(
                  padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                  decoration: BoxDecoration(
                    color: const Color(0xFFF1F5F9),
                    borderRadius: BorderRadius.circular(20),
                    border: Border.all(color: const Color(0xFFE2E8F0)),
                  ),
                  child: Text(
                    config.badgeText!,
                    style: const TextStyle(
                      fontSize: 11,
                      fontWeight: FontWeight.w700,
                      color: Color(0xFF475569),
                    ),
                  ),
                ),
                const SizedBox(height: 12),
              ],

              // 3. Title
              Text(
                config.title,
                textAlign: TextAlign.center,
                style: TextStyle(
                  fontSize: widget.isCompact ? 18 : 22,
                  fontWeight: FontWeight.w800,
                  color: const Color(0xFF0F172A),
                  letterSpacing: -0.4,
                ),
              ),

              const SizedBox(height: 8),

              // 4. Description Message
              Text(
                config.message,
                textAlign: TextAlign.center,
                style: TextStyle(
                  fontSize: widget.isCompact ? 12.5 : 13.5,
                  color: const Color(0xFF64748B),
                  height: 1.45,
                ),
              ),

              // 5. Checklist / Tips Card (Only in full view)
              if (!widget.isCompact && config.tips != null && config.tips!.isNotEmpty) ...[
                const SizedBox(height: 20),
                Container(
                  padding: const EdgeInsets.all(14),
                  decoration: BoxDecoration(
                    color: Colors.white,
                    borderRadius: BorderRadius.circular(14),
                    border: Border.all(color: const Color(0xFFE2E8F0)),
                  ),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      const Row(
                        children: [
                          Icon(Icons.lightbulb_outline_rounded, size: 15, color: Color(0xFFD97706)),
                          SizedBox(width: 6),
                          Text(
                            'Saran Langkah Perbaikan:',
                            style: TextStyle(
                              fontSize: 12,
                              fontWeight: FontWeight.w700,
                              color: Color(0xFF334155),
                            ),
                          ),
                        ],
                      ),
                      const SizedBox(height: 8),
                      ...config.tips!.map(
                        (tip) => Padding(
                          padding: const EdgeInsets.only(bottom: 5),
                          child: Row(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              const Text('• ', style: TextStyle(color: Color(0xFF059669), fontWeight: FontWeight.w900)),
                              Expanded(
                                child: Text(
                                  tip,
                                  style: const TextStyle(
                                    fontSize: 12,
                                    color: Color(0xFF475569),
                                    height: 1.35,
                                  ),
                                ),
                              ),
                            ],
                          ),
                        ),
                      ),
                    ],
                  ),
                ),
              ],

              const SizedBox(height: 24),

              // 6. Action Buttons
              if (widget.onRetry != null) ...[
                SizedBox(
                  width: double.infinity,
                  height: 48,
                  child: FilledButton(
                    onPressed: _isLoading ? null : _handleRetry,
                    style: FilledButton.styleFrom(
                      backgroundColor: const Color(0xFF059669),
                      foregroundColor: Colors.white,
                      elevation: 0,
                      shape: RoundedRectangleBorder(
                        borderRadius: BorderRadius.circular(14),
                      ),
                    ),
                    child: _isLoading
                        ? const SizedBox.square(
                            dimension: 20,
                            child: CircularProgressIndicator(
                              strokeWidth: 2.2,
                              color: Colors.white,
                            ),
                          )
                        : Row(
                            mainAxisAlignment: MainAxisAlignment.center,
                            children: [
                              const Icon(Icons.refresh_rounded, size: 18),
                              const SizedBox(width: 8),
                              Text(
                                config.primaryButtonText,
                                style: const TextStyle(
                                  fontSize: 14.5,
                                  fontWeight: FontWeight.w700,
                                ),
                              ),
                            ],
                          ),
                  ),
                ),
              ],

              if (widget.onSecondaryAction != null && config.secondaryButtonText != null) ...[
                const SizedBox(height: 10),
                TextButton(
                  onPressed: _isLoading ? null : widget.onSecondaryAction,
                  style: TextButton.styleFrom(
                    foregroundColor: const Color(0xFF64748B),
                    textStyle: const TextStyle(
                      fontSize: 13.5,
                      fontWeight: FontWeight.w600,
                    ),
                  ),
                  child: Text(config.secondaryButtonText!),
                ),
              ],
            ],
          ),
        ),
      ),
    );
  }
}
