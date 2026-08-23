import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';

import 'package:padi/core/network/api_client.dart';
import 'package:padi/core/storage/token_storage.dart';
import 'package:padi/features/auth/presentation/widgets/padi_theme.dart';
import 'package:padi/features/marketplace/data/services/marketplace_api_service.dart';

class CreateMarketOfferScreen extends StatefulWidget {
  const CreateMarketOfferScreen({
    super.key,
    required this.listingId,
    required this.commodity,
    required this.unit,
    required this.maxQuantity,
    required this.referencePrice,
  });

  final int listingId;
  final String commodity;
  final String unit;
  final double maxQuantity;
  final double referencePrice;

  @override
  State<CreateMarketOfferScreen> createState() =>
      _CreateMarketOfferScreenState();
}

class _CreateMarketOfferScreenState
    extends State<CreateMarketOfferScreen> {
  late final MarketplaceApiService _service;

  final _formKey = GlobalKey<FormState>();

  final _priceController = TextEditingController();
  final _quantityController = TextEditingController();
  final _messageController = TextEditingController();

  bool _isLoading = false;

  @override
  void initState() {
    super.initState();

    _service = MarketplaceApiService(
      ApiClient(
        const SecureTokenStorage(),
      ),
    );

    _priceController.text =
        widget.referencePrice.toStringAsFixed(0);

    _quantityController.text =
        widget.maxQuantity.toStringAsFixed(0);
  }

  @override
  void dispose() {
    _priceController.dispose();
    _quantityController.dispose();
    _messageController.dispose();
    super.dispose();
  }

  Future<void> _submit() async {
    if (!_formKey.currentState!.validate()) {
      return;
    }

    final price = double.parse(
      _priceController.text.replaceAll(',', '.'),
    );

    final quantity = double.parse(
      _quantityController.text.replaceAll(',', '.'),
    );

    setState(() {
      _isLoading = true;
    });

    try {
      await _service.createOffer(
        listingId: widget.listingId,
        offeredPrice: price,
        quantity: quantity,
        message: _messageController.text.trim().isEmpty
            ? null
            : _messageController.text.trim(),
      );

      if (!mounted) {
        return;
      }

      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text(
            'Penawaran berhasil dikirim.',
          ),
        ),
      );

      context.pop(true);
    } catch (e) {
      if (!mounted) {
        return;
      }

      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text(
            e.toString().replaceFirst(
              'Exception: ',
              '',
            ),
          ),
        ),
      );
    } finally {
      if (mounted) {
        setState(() {
          _isLoading = false;
        });
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: padiField,
      appBar: AppBar(
        backgroundColor: padiGreen,
        foregroundColor: Colors.white,
        elevation: 0,
        title: const Text(
          'Ajukan Penawaran',
          style: TextStyle(
            fontWeight: FontWeight.w900,
          ),
        ),
      ),
      body: Form(
        key: _formKey,
        child: ListView(
          padding: const EdgeInsets.all(20),
          children: [
            Container(
              padding: const EdgeInsets.all(20),
              decoration: BoxDecoration(
                color: padiGreen,
                borderRadius: BorderRadius.circular(24),
              ),
              child: Row(
                children: [
                  const Icon(
                    Icons.storefront_rounded,
                    color: Colors.white,
                    size: 38,
                  ),
                  const SizedBox(width: 14),
                  Expanded(
                    child: Column(
                      crossAxisAlignment:
                          CrossAxisAlignment.start,
                      children: [
                        const Text(
                          'Ajukan Penawaran',
                          style: TextStyle(
                            color: Colors.white,
                            fontSize: 20,
                            fontWeight: FontWeight.w900,
                          ),
                        ),
                        const SizedBox(height: 5),
                        Text(
                          widget.commodity,
                          style: const TextStyle(
                            color: Colors.white,
                            fontSize: 15,
                          ),
                        ),
                      ],
                    ),
                  ),
                ],
              ),
            ),
            const SizedBox(height: 24),
            TextFormField(
              controller: _priceController,
              keyboardType:
                  const TextInputType.numberWithOptions(
                decimal: true,
              ),
              decoration: const InputDecoration(
                labelText: 'Harga Penawaran',
                prefixText: 'Rp ',
                suffixText: ' / kg',
              ),
              validator: (value) {
                final text = value?.trim() ?? '';

                if (text.isEmpty) {
                  return 'Harga penawaran wajib diisi.';
                }

                final price = double.tryParse(
                  text.replaceAll(',', '.'),
                );

                if (price == null || price <= 0) {
                  return 'Masukkan harga yang valid.';
                }

                return null;
              },
            ),
            const SizedBox(height: 18),
            TextFormField(
              controller: _quantityController,
              keyboardType:
                  const TextInputType.numberWithOptions(
                decimal: true,
              ),
              decoration: InputDecoration(
                labelText: 'Jumlah',
                suffixText: widget.unit,
              ),
              validator: (value) {
                final text = value?.trim() ?? '';

                if (text.isEmpty) {
                  return 'Jumlah wajib diisi.';
                }

                final quantity = double.tryParse(
                  text.replaceAll(',', '.'),
                );

                if (quantity == null || quantity <= 0) {
                  return 'Masukkan jumlah yang valid.';
                }

                if (quantity > widget.maxQuantity) {
                  return 'Jumlah melebihi stok tersedia.';
                }

                return null;
              },
            ),
            const SizedBox(height: 18),
            TextFormField(
              controller: _messageController,
              maxLines: 5,
              decoration: const InputDecoration(
                labelText: 'Pesan',
                hintText:
                    'Contoh: Saya berminat membeli hasil panen ini.',
                alignLabelWithHint: true,
              ),
            ),
            const SizedBox(height: 28),
            Container(
              padding: const EdgeInsets.all(18),
              decoration: BoxDecoration(
                color: Colors.white,
                borderRadius: BorderRadius.circular(18),
              ),
              child: Row(
                children: [
                  const Icon(
                    Icons.info_outline_rounded,
                    color: padiGreen,
                  ),
                  const SizedBox(width: 12),
                  Expanded(
                    child: Text(
                      'Harga patokan petani: Rp${widget.referencePrice.toStringAsFixed(0)} / ${widget.unit}',
                      style: const TextStyle(
                        color: padiMuted,
                        fontSize: 14,
                      ),
                    ),
                  ),
                ],
              ),
            ),
            const SizedBox(height: 28),
            FilledButton(
              onPressed: _isLoading ? null : _submit,
              child: _isLoading
                  ? const SizedBox(
                      width: 22,
                      height: 22,
                      child: CircularProgressIndicator(
                        strokeWidth: 2.5,
                        color: Colors.white,
                      ),
                    )
                  : const Text(
                      'Kirim Penawaran',
                    ),
            ),
          ],
        ),
      ),
    );
  }
}