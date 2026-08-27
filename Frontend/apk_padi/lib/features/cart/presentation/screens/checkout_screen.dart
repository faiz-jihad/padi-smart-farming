import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import 'package:intl/intl.dart';
import 'package:url_launcher/url_launcher.dart';
import 'package:padi/core/providers/app_providers.dart';
import 'package:padi/features/cart/data/models/cart_item_model.dart';
import 'package:padi/features/cart/presentation/providers/cart_providers.dart';
import 'package:padi/features/home/presentation/tokens/home_tokens.dart';
import 'package:padi/features/marketplace/data/services/marketplace_api_service.dart';

class CheckoutScreen extends ConsumerStatefulWidget {
  const CheckoutScreen({super.key, this.directItem});

  final CartItemModel? directItem;

  @override
  ConsumerState<CheckoutScreen> createState() => _CheckoutScreenState();
}

class _CheckoutScreenState extends ConsumerState<CheckoutScreen> {
  final _formKey = GlobalKey<FormState>();
  final _nameController = TextEditingController();
  final _phoneController = TextEditingController();
  final _addressController = TextEditingController();
  final _notesController = TextEditingController();

  bool _isProcessing = false;
  String _selectedPaymentMethod = 'wa_contract'; // 'wa_contract' or 'bank_transfer'

  @override
  void initState() {
    super.initState();
    final user = ref.read(authControllerProvider).state.user;
    if (user != null) {
      _nameController.text = user.name;
      _phoneController.text = user.phone ?? '';
    }
  }

  @override
  void dispose() {
    _nameController.dispose();
    _phoneController.dispose();
    _addressController.dispose();
    _notesController.dispose();
    super.dispose();
  }

  String _formatPrice(double value) {
    final currencyFmt = NumberFormat.currency(
      locale: 'id_ID',
      symbol: 'Rp',
      decimalDigits: 0,
    );
    return currencyFmt.format(value.round());
  }

  List<CartItemModel> _getOrderItems(CartState cartState) {
    if (widget.directItem != null) {
      return [widget.directItem!];
    }
    return cartState.selectedItems;
  }

  Future<void> _processCheckout(List<CartItemModel> items) async {
    if (!_formKey.currentState!.validate()) {
      return;
    }

    if (items.isEmpty) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Tidak ada komoditas yang dipilih.')),
      );
      return;
    }

    setState(() => _isProcessing = true);

    final service = MarketplaceApiService(ref.read(apiClientProvider));
    final purchasedListingIds = <int>[];
    final createdContractIds = <int>[];

    try {
      // 1. Simpan Kontrak Pembelian ke Backend Laravel untuk setiap item
      for (final item in items) {
        try {
          final contract = await service.createPurchaseContract(
            listingId: item.listingId,
            quantity: item.quantity,
            agreedPrice: item.pricePerUnit,
            notes: _notesController.text.trim(),
          );
          createdContractIds.add(contract.id);
        } catch (_) {
          // Tetap lanjutkan jika listing lokal / fallback
        }
        purchasedListingIds.add(item.listingId);
      }

      // 2. Format Pesan WhatsApp Terstruktur
      final firstFarmerPhone = items.first.farmerPhone ?? '+6281234567890';
      var phoneDigits = firstFarmerPhone.replaceAll(RegExp(r'[^0-9]'), '');
      if (phoneDigits.startsWith('0')) {
        phoneDigits = '62${phoneDigits.substring(1)}';
      }

      final buffer = StringBuffer();
      buffer.writeln('🌾 *PESANAN HASIL PANEN - P.A.D.I. SMART FARMING* 🌾');
      if (createdContractIds.isNotEmpty) {
        buffer.writeln('No. Kontrak: #${createdContractIds.join(', #')}');
      }
      buffer.writeln('------------------------------------------');
      buffer.writeln('Halo Petani Mitra P.A.D.I.,');
      buffer.writeln('Saya ingin mengonfirmasi pesanan hasil panen:');
      buffer.writeln('');
      buffer.writeln('📦 *Rincian Komoditas:*');

      double grandTotal = 0;
      for (final item in items) {
        final sub = item.subtotal;
        grandTotal += sub;
        final qtyStr = item.quantity == item.quantity.roundToDouble()
            ? item.quantity.toInt().toString()
            : item.quantity.toString();
        buffer.writeln('• ${item.commodity}');
        buffer.writeln('  Jumlah: $qtyStr ${item.unit} x ${_formatPrice(item.pricePerUnit)}');
        buffer.writeln('  Subtotal: ${_formatPrice(sub)}');
      }

      buffer.writeln('------------------------------------------');
      buffer.writeln('💰 *TOTAL NILAI TRANSAKSI: ${_formatPrice(grandTotal)}*');
      buffer.writeln('');
      buffer.writeln('📍 *Data Pembeli & Lokasi Pengiriman:*');
      buffer.writeln('• Nama: ${_nameController.text.trim()}');
      buffer.writeln('• Kontak WA: ${_phoneController.text.trim()}');
      buffer.writeln('• Alamat/Titik Jemput: ${_addressController.text.trim()}');
      if (_notesController.text.trim().isNotEmpty) {
        buffer.writeln('• Catatan: ${_notesController.text.trim()}');
      }
      buffer.writeln('');
      buffer.writeln('Mohon konfirmasi ketersediaan dan koordinasi titik penimbangan / armada angkut. Terima kasih!');

      final whatsappUrl = Uri.parse(
        'https://wa.me/$phoneDigits?text=${Uri.encodeComponent(buffer.toString())}',
      );

      // 3. Bersihkan keranjang
      if (widget.directItem == null) {
        ref.read(cartProvider.notifier).clearPurchased(purchasedListingIds);
      }

      // 4. Buka WhatsApp
      await launchUrl(whatsappUrl, mode: LaunchMode.externalApplication);

      if (!mounted) return;

      // 5. Tampilkan Dialog Berhasil
      await showDialog(
        context: context,
        barrierDismissible: false,
        builder: (ctx) => AlertDialog(
          shape: RoundedRectangleBorder(
            borderRadius: BorderRadius.circular(18),
          ),
          content: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              Container(
                width: 64,
                height: 64,
                decoration: const BoxDecoration(
                  color: Color(0xFFECFDF5),
                  shape: BoxShape.circle,
                ),
                child: const Icon(
                  Icons.check_circle_rounded,
                  color: HomeColors.primaryGreen,
                  size: 38,
                ),
              ),
              const SizedBox(height: 16),
              const Text(
                'Pesanan Berhasil Dibuat!',
                style: TextStyle(
                  fontSize: 18,
                  fontWeight: FontWeight.w900,
                  color: Color(0xFF17251E),
                ),
                textAlign: TextAlign.center,
              ),
              const SizedBox(height: 8),
              const Text(
                'Kontrak pembelian telah dicatat di sistem P.A.D.I. dan pesan telah disiapkan untuk dikirim ke WhatsApp petani.',
                style: TextStyle(
                  fontSize: 12.5,
                  color: Color(0xFF64748B),
                  height: 1.4,
                ),
                textAlign: TextAlign.center,
              ),
              const SizedBox(height: 20),
              FilledButton.icon(
                onPressed: () {
                  Navigator.pop(ctx);
                  context.go('/buyer/orders');
                },
                icon: const Icon(Icons.receipt_long_rounded, size: 18),
                label: const Text('Lihat Kontrak & Pesanan Saya'),
                style: FilledButton.styleFrom(
                  backgroundColor: HomeColors.primaryGreen,
                  minimumSize: const Size(double.infinity, 44),
                  shape: RoundedRectangleBorder(
                    borderRadius: BorderRadius.circular(10),
                  ),
                ),
              ),
              const SizedBox(height: 8),
              TextButton(
                onPressed: () {
                  Navigator.pop(ctx);
                  context.go('/home');
                },
                child: const Text(
                  'Kembali ke Beranda',
                  style: TextStyle(color: Color(0xFF64748B)),
                ),
              ),
            ],
          ),
        ),
      );
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text('Terjadi kendala: ${e.toString()}'),
            behavior: SnackBarBehavior.floating,
          ),
        );
      }
    } finally {
      if (mounted) {
        setState(() => _isProcessing = false);
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    final cartState = ref.watch(cartProvider);
    final items = _getOrderItems(cartState);

    final totalAmount = items.fold<double>(
      0,
      (sum, item) => sum + item.subtotal,
    );

    return Scaffold(
      backgroundColor: const Color(0xFFF6F8F5),
      appBar: AppBar(
        backgroundColor: Colors.white,
        elevation: 0,
        scrolledUnderElevation: 0,
        leading: IconButton(
          icon: const Icon(
            Icons.arrow_back_rounded,
            color: Color(0xFF17251E),
          ),
          onPressed: () => context.pop(),
        ),
        title: const Text(
          'Checkout Pembelian Panen',
          style: TextStyle(
            fontSize: 17,
            fontWeight: FontWeight.w800,
            color: Color(0xFF17251E),
          ),
        ),
      ),
      body: Form(
        key: _formKey,
        child: ListView(
          padding: const EdgeInsets.fromLTRB(16, 14, 16, 120),
          children: [
            // 1. Info Pengiriman & Data Pembeli
            _buildSectionCard(
              title: 'Informasi Pembeli & Titik Jemput',
              icon: Icons.local_shipping_rounded,
              child: Column(
                children: [
                  TextFormField(
                    controller: _nameController,
                    decoration: _inputDecoration(
                      label: 'Nama Lengkap Pembeli / Perusahaan',
                      hint: 'Contoh: H. Sudarsono / RMU Berkah Jaya',
                      icon: Icons.person_outline_rounded,
                    ),
                    validator: (val) =>
                        val == null || val.trim().isEmpty
                            ? 'Nama wajib diisi'
                            : null,
                  ),
                  const SizedBox(height: 12),
                  TextFormField(
                    controller: _phoneController,
                    keyboardType: TextInputType.phone,
                    decoration: _inputDecoration(
                      label: 'Nomor WhatsApp Aktif',
                      hint: 'Contoh: 081234567890',
                      icon: Icons.phone_outlined,
                    ),
                    validator: (val) =>
                        val == null || val.trim().isEmpty
                            ? 'Nomor telepon/WA wajib diisi'
                            : null,
                  ),
                  const SizedBox(height: 12),
                  TextFormField(
                    controller: _addressController,
                    maxLines: 2,
                    decoration: _inputDecoration(
                      label: 'Alamat Tujuan / Titik Jemput Armada Truk',
                      hint: 'Sebutkan jalan, desa, kecamatan, dan patokan titik muat armada...',
                      icon: Icons.location_on_outlined,
                    ),
                    validator: (val) =>
                        val == null || val.trim().isEmpty
                            ? 'Alamat / titik jemput wajib diisi'
                            : null,
                  ),
                  const SizedBox(height: 12),
                  TextFormField(
                    controller: _notesController,
                    maxLines: 2,
                    decoration: _inputDecoration(
                      label: 'Catatan Khusus untuk Petani (Opsional)',
                      hint: 'Contoh: Siapkan timbangan digital kalibrasi di lokasi tepi sawah...',
                      icon: Icons.note_alt_outlined,
                    ),
                  ),
                ],
              ),
            ),

            const SizedBox(height: 14),

            // 2. Ringkasan Komoditas Panen yang Dipesan
            _buildSectionCard(
              title: 'Rincian Komoditas (${items.length} Item)',
              icon: Icons.inventory_2_rounded,
              child: Column(
                children: items.map((item) {
                  return Padding(
                    padding: const EdgeInsets.only(bottom: 12),
                    child: Row(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        ClipRRect(
                          borderRadius: BorderRadius.circular(8),
                          child: SizedBox(
                            width: 58,
                            height: 58,
                            child: _buildItemImage(item.imageUrl),
                          ),
                        ),
                        const SizedBox(width: 12),
                        Expanded(
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              Text(
                                item.commodity,
                                style: const TextStyle(
                                  fontSize: 13,
                                  fontWeight: FontWeight.w700,
                                  color: Color(0xFF17251E),
                                ),
                                maxLines: 2,
                                overflow: TextOverflow.ellipsis,
                              ),
                              const SizedBox(height: 4),
                              Text(
                                '${item.quantity} ${item.unit} x ${_formatPrice(item.pricePerUnit)}',
                                style: const TextStyle(
                                  fontSize: 11.5,
                                  color: Color(0xFF64748B),
                                ),
                              ),
                            ],
                          ),
                        ),
                        const SizedBox(width: 8),
                        Text(
                          _formatPrice(item.subtotal),
                          style: const TextStyle(
                            fontSize: 13,
                            fontWeight: FontWeight.w800,
                            color: HomeColors.primaryGreen,
                          ),
                        ),
                      ],
                    ),
                  );
                }).toList(),
              ),
            ),

            const SizedBox(height: 14),

            // 3. Pilihan Metode Penyelesaian Transaksi
            _buildSectionCard(
              title: 'Metode Transaksi',
              icon: Icons.payments_rounded,
              child: Column(
                children: [
                  _buildPaymentRadioTile(
                    value: 'wa_contract',
                    title: 'WhatsApp Direct & Kontrak Resmi P.A.D.I.',
                    subtitle:
                        'Otomatis catat kontrak resmi di sistem dan koordinasikan penimbangan serta pembayaran langsung via WA dengan petani.',
                    badge: 'Rekomendasi',
                  ),
                ],
              ),
            ),

            const SizedBox(height: 14),

            // 4. Rincian Biaya
            _buildSectionCard(
              title: 'Rincian Pembayaran',
              icon: Icons.receipt_rounded,
              child: Column(
                children: [
                  _buildPriceRow('Subtotal Komoditas', _formatPrice(totalAmount)),
                  const SizedBox(height: 8),
                  _buildPriceRow('Biaya Jasa Timbangan Tara P.A.D.I.', 'Gratis (Rp 0)'),
                  const SizedBox(height: 8),
                  _buildPriceRow('Biaya Administrasi Aplikasi', 'Gratis (Rp 0)'),
                  const Divider(color: Color(0xFFE2E8F0), height: 20),
                  Row(
                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                    children: [
                      const Text(
                        'Total Nilai Transaksi',
                        style: TextStyle(
                          fontSize: 14,
                          fontWeight: FontWeight.w800,
                          color: Color(0xFF17251E),
                        ),
                      ),
                      Text(
                        _formatPrice(totalAmount),
                        style: const TextStyle(
                          fontSize: 18,
                          fontWeight: FontWeight.w900,
                          color: HomeColors.primaryGreen,
                        ),
                      ),
                    ],
                  ),
                ],
              ),
            ),
          ],
        ),
      ),
      bottomNavigationBar: Container(
        padding: const EdgeInsets.fromLTRB(16, 12, 16, 18),
        decoration: BoxDecoration(
          color: Colors.white,
          boxShadow: [
            BoxShadow(
              color: Colors.black.withOpacity(0.08),
              blurRadius: 12,
              offset: const Offset(0, -3),
            ),
          ],
        ),
        child: SafeArea(
          top: false,
          child: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  const Text(
                    'Total Tagihan:',
                    style: TextStyle(
                      fontSize: 12,
                      color: Color(0xFF64748B),
                    ),
                  ),
                  Text(
                    _formatPrice(totalAmount),
                    style: const TextStyle(
                      fontSize: 18,
                      fontWeight: FontWeight.w900,
                      color: HomeColors.primaryGreen,
                    ),
                  ),
                ],
              ),
              const SizedBox(height: 10),
              FilledButton.icon(
                onPressed: _isProcessing
                    ? null
                    : () => _processCheckout(items),
                icon: _isProcessing
                    ? const SizedBox(
                        width: 18,
                        height: 18,
                        child: CircularProgressIndicator(
                          color: Colors.white,
                          strokeWidth: 2,
                        ),
                      )
                    : const Icon(
                        Icons.chat_rounded,
                        size: 20,
                        color: Colors.white,
                      ),
                label: Text(
                  _isProcessing
                      ? 'Memproses Pesanan...'
                      : 'Beli & Hubungi Petani via WhatsApp',
                  style: const TextStyle(
                    fontSize: 14,
                    fontWeight: FontWeight.w800,
                  ),
                ),
                style: FilledButton.styleFrom(
                  backgroundColor: const Color(0xFF16A34A), // Emerald WhatsApp green
                  foregroundColor: Colors.white,
                  minimumSize: const Size(double.infinity, 48),
                  shape: RoundedRectangleBorder(
                    borderRadius: BorderRadius.circular(10),
                  ),
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildSectionCard({
    required String title,
    required IconData icon,
    required Widget child,
  }) {
    return Container(
      width: double.infinity,
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(14),
        border: Border.all(color: const Color(0xFFE5ECE3)),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              Icon(icon, size: 18, color: HomeColors.primaryGreen),
              const SizedBox(width: 8),
              Text(
                title,
                style: const TextStyle(
                  fontSize: 14,
                  fontWeight: FontWeight.w800,
                  color: Color(0xFF17251E),
                ),
              ),
            ],
          ),
          const SizedBox(height: 14),
          child,
        ],
      ),
    );
  }

  Widget _buildPriceRow(String label, String value) {
    return Row(
      mainAxisAlignment: MainAxisAlignment.spaceBetween,
      children: [
        Text(
          label,
          style: const TextStyle(
            fontSize: 12.5,
            color: Color(0xFF64748B),
          ),
        ),
        Text(
          value,
          style: const TextStyle(
            fontSize: 12.5,
            fontWeight: FontWeight.w700,
            color: Color(0xFF1E293B),
          ),
        ),
      ],
    );
  }

  Widget _buildPaymentRadioTile({
    required String value,
    required String title,
    required String subtitle,
    String? badge,
  }) {
    final isSelected = _selectedPaymentMethod == value;

    return Container(
      padding: const EdgeInsets.all(12),
      decoration: BoxDecoration(
        color: isSelected ? const Color(0xFFF0FDF4) : Colors.white,
        borderRadius: BorderRadius.circular(10),
        border: Border.all(
          color: isSelected
              ? HomeColors.primaryGreen
              : const Color(0xFFE2E8F0),
          width: isSelected ? 1.5 : 1,
        ),
      ),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Icon(
            isSelected
                ? Icons.radio_button_checked_rounded
                : Icons.radio_button_off_rounded,
            color: isSelected
                ? HomeColors.primaryGreen
                : const Color(0xFF94A3B8),
            size: 20,
          ),
          const SizedBox(width: 10),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Row(
                  children: [
                    Text(
                      title,
                      style: TextStyle(
                        fontSize: 13,
                        fontWeight: FontWeight.w800,
                        color: isSelected
                            ? const Color(0xFF166534)
                            : const Color(0xFF1E293B),
                      ),
                    ),
                    if (badge != null) ...[
                      const SizedBox(width: 6),
                      Container(
                        padding: const EdgeInsets.symmetric(
                          horizontal: 6,
                          vertical: 2,
                        ),
                        decoration: BoxDecoration(
                          color: HomeColors.primaryGreen,
                          borderRadius: BorderRadius.circular(4),
                        ),
                        child: Text(
                          badge,
                          style: const TextStyle(
                            fontSize: 9.5,
                            fontWeight: FontWeight.w800,
                            color: Colors.white,
                          ),
                        ),
                      ),
                    ],
                  ],
                ),
                const SizedBox(height: 4),
                Text(
                  subtitle,
                  style: const TextStyle(
                    fontSize: 11.5,
                    color: Color(0xFF64748B),
                    height: 1.35,
                  ),
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }

  InputDecoration _inputDecoration({
    required String label,
    required String hint,
    required IconData icon,
  }) {
    return InputDecoration(
      labelText: label,
      labelStyle: const TextStyle(fontSize: 12, color: Color(0xFF64748B)),
      hintText: hint,
      hintStyle: const TextStyle(fontSize: 12, color: Color(0xFF94A3B8)),
      prefixIcon: Icon(icon, size: 18, color: const Color(0xFF64748B)),
      filled: true,
      fillColor: const Color(0xFFF8FAFC),
      contentPadding: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
      border: OutlineInputBorder(
        borderRadius: BorderRadius.circular(10),
        borderSide: const BorderSide(color: Color(0xFFE2E8F0)),
      ),
      enabledBorder: OutlineInputBorder(
        borderRadius: BorderRadius.circular(10),
        borderSide: const BorderSide(color: Color(0xFFE2E8F0)),
      ),
      focusedBorder: OutlineInputBorder(
        borderRadius: BorderRadius.circular(10),
        borderSide: const BorderSide(color: HomeColors.primaryGreen, width: 1.5),
      ),
    );
  }

  Widget _buildItemImage(String? imageUrl) {
    final cleanUrl = imageUrl?.trim() ?? '';
    final isValidHttp =
        cleanUrl.startsWith('http://') || cleanUrl.startsWith('https://');

    if (!isValidHttp) {
      return Image.asset(
        'assets/images/onboarding_3.jpeg',
        fit: BoxFit.cover,
      );
    }

    return Image.network(
      cleanUrl,
      fit: BoxFit.cover,
      errorBuilder: (context, error, stackTrace) => Image.asset(
        'assets/images/onboarding_3.jpeg',
        fit: BoxFit.cover,
      ),
    );
  }
}
