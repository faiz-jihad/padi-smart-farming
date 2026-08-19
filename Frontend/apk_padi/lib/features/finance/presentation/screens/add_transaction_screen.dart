import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';
import 'package:padi/features/auth/presentation/widgets/padi_theme.dart';
import 'package:padi/features/finance/presentation/widgets/finance_type_selector.dart';

class AddTransactionScreen extends StatefulWidget {
  const AddTransactionScreen({super.key});

  @override
  State<AddTransactionScreen> createState() => _AddTransactionScreenState();
}

class _AddTransactionScreenState extends State<AddTransactionScreen> {
  final TextEditingController _titleController = TextEditingController();
  final TextEditingController _amountController = TextEditingController();
  final TextEditingController _noteController = TextEditingController();

  String _type = 'Pengeluaran';

  @override
  void dispose() {
    _titleController.dispose();
    _amountController.dispose();
    _noteController.dispose();
    super.dispose();
  }

  void _saveTransaction() {
    final title = _titleController.text.trim();
    final amount = double.tryParse(
      _amountController.text.trim().replaceAll('.', ''),
    );

    if (title.isEmpty || amount == null || amount <= 0) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text('Lengkapi data transaksi terlebih dahulu.'),
          behavior: SnackBarBehavior.floating,
        ),
      );
      return;
    }

    context.pop();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: padiField,
      appBar: AppBar(
        backgroundColor: padiField,
        elevation: 0,
        scrolledUnderElevation: 0,
        leading: IconButton(
          onPressed: () => context.pop(),
          icon: const Icon(
            Icons.arrow_back_rounded,
            color: padiInk,
          ),
        ),
        title: const Text(
          'Tambah Transaksi',
          style: TextStyle(
            color: padiInk,
            fontSize: 20,
            fontWeight: FontWeight.w900,
          ),
        ),
      ),
      body: ListView(
        padding: const EdgeInsets.fromLTRB(20, 8, 20, 30),
        children: [
          const Text(
            'Catat transaksi',
            style: TextStyle(
              color: padiInk,
              fontSize: 24,
              fontWeight: FontWeight.w900,
            ),
          ),
          const SizedBox(height: 6),
          const Text(
            'Simpan pemasukan atau pengeluaran pertanian Anda.',
            style: TextStyle(
              color: padiMuted,
              fontSize: 13,
            ),
          ),
          const SizedBox(height: 22),
          FinanceTypeSelector(
            selectedType: _type,
            onChanged: (value) {
              setState(() {
                _type = value;
              });
            },
          ),
          const SizedBox(height: 18),
          const Text(
            'Nama Transaksi',
            style: TextStyle(
              color: padiInk,
              fontSize: 14,
              fontWeight: FontWeight.w800,
            ),
          ),
          const SizedBox(height: 8),
          TextField(
            controller: _titleController,
            decoration: const InputDecoration(
              hintText: 'Contoh: Pembelian pupuk',
              prefixIcon: Icon(
                Icons.edit_note_rounded,
                color: padiGreen,
              ),
            ),
          ),
          const SizedBox(height: 18),
          const Text(
            'Jumlah',
            style: TextStyle(
              color: padiInk,
              fontSize: 14,
              fontWeight: FontWeight.w800,
            ),
          ),
          const SizedBox(height: 8),
          TextField(
            controller: _amountController,
            keyboardType: TextInputType.number,
            decoration: const InputDecoration(
              hintText: 'Contoh: 500000',
              prefixIcon: Icon(
                Icons.payments_rounded,
                color: padiGreen,
              ),
              prefixText: 'Rp ',
            ),
          ),
          const SizedBox(height: 18),
          const Text(
            'Catatan',
            style: TextStyle(
              color: padiInk,
              fontSize: 14,
              fontWeight: FontWeight.w800,
            ),
          ),
          const SizedBox(height: 8),
          TextField(
            controller: _noteController,
            maxLines: 4,
            decoration: const InputDecoration(
              hintText: 'Tambahkan catatan jika diperlukan',
              alignLabelWithHint: true,
            ),
          ),
          const SizedBox(height: 24),
          SizedBox(
            width: double.infinity,
            height: 54,
            child: FilledButton.icon(
              onPressed: _saveTransaction,
              icon: const Icon(Icons.save_rounded),
              label: const Text(
                'Simpan Transaksi',
                style: TextStyle(
                  fontWeight: FontWeight.w900,
                ),
              ),
            ),
          ),
        ],
      ),
    );
  }
} 