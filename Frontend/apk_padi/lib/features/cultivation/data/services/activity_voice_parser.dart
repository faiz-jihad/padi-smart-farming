class ParsedActivityVoiceResult {
  const ParsedActivityVoiceResult({
    required this.hasRecognizedActivity,
    required this.activityKey,
    required this.activityLabel,
    required this.fieldLabel,
    required this.notes,
    required this.transcript,
    required this.shouldSave,
    this.cost,
  });

  final bool hasRecognizedActivity;
  final String activityKey;
  final String activityLabel;
  final String fieldLabel;
  final String notes;
  final String transcript;
  final bool shouldSave;
  final int? cost;
}

class ActivityVoiceParser {
  static const Map<String, List<String>> keywordActivityMap = {
    'planting': [
      'tanam',
      'menanam',
      'ditanam',
      'pindah tanam',
      'semai',
      'menyemai',
    ],
    'fertilizing': ['pupuk', 'memupuk', 'pemupukan'],
    'irrigation': ['air', 'mengairi', 'pengairan', 'irigasi', 'genangan'],
    'spraying': ['semprot', 'menyemprot', 'penyemprotan', 'obat tanaman'],
    'other': ['penyiangan', 'menyiangi', 'gulma', 'rumput'],
    'land_preparation': ['bajak', 'garu', 'pematang', 'olah lahan'],
  };

  static const Map<String, String> activityLabels = {
    'planting': 'Penanaman',
    'fertilizing': 'Pemupukan',
    'irrigation': 'Pengairan',
    'spraying': 'Penyemprotan',
    'other': 'Penyiangan',
    'land_preparation': 'Pengolahan Lahan',
  };

  static ParsedActivityVoiceResult parse(String transcript) {
    final rawText = transcript.trim();
    final normalized = rawText.toLowerCase();

    String? matchedActivityKey;

    for (final entry in keywordActivityMap.entries) {
      final keywords = entry.value;
      final matched = keywords.any((keyword) => normalized.contains(keyword));

      if (matched) {
        matchedActivityKey = entry.key;
        break;
      }
    }

    final fieldLabel = _extractFieldLabel(normalized) ?? 'Lahan aktif';
    final activityKey = matchedActivityKey ?? 'other';
    final cost = _extractCost(normalized);

    return ParsedActivityVoiceResult(
      hasRecognizedActivity: matchedActivityKey != null,
      activityKey: activityKey,
      activityLabel: activityLabels[activityKey] ?? 'Aktivitas Lainnya',
      fieldLabel: fieldLabel,
      notes: rawText,
      transcript: rawText,
      shouldSave: RegExp(r'\b(simpan|simpan kegiatan)\b').hasMatch(normalized),
      cost: cost,
    );
  }

  static int? _extractCost(String text) {
    final match = RegExp(
      r'\b(?:nominal\s+harga|harga|biaya|nominal)\s+(?:rp\s*)?([0-9][0-9.\s,]*)',
    ).firstMatch(text);
    if (match == null) return null;

    return int.tryParse(match.group(1)!.replaceAll(RegExp(r'[^0-9]'), ''));
  }

  static String? _extractFieldLabel(String normalizedText) {
    final patterns = <RegExp>[
      RegExp(r'\blahan\s+(satu|1)\b'),
      RegExp(r'\blahan\s+(dua|2)\b'),
      RegExp(r'\blahan\s+(tiga|3)\b'),
      RegExp(r'\blahan\s+(empat|4)\b'),
      RegExp(r'\blahan\s+(lima|5)\b'),
      RegExp(r'\blahan\s+(enam|6)\b'),
      RegExp(r'\blahan\s+(tujuh|7)\b'),
      RegExp(r'\blahan\s+(delapan|8)\b'),
      RegExp(r'\blahan\s+(sembilan|9)\b'),
      RegExp(r'\blahan\s+(sepuluh|10)\b'),
    ];

    for (final pattern in patterns) {
      final match = pattern.firstMatch(normalizedText);
      if (match != null) {
        final matchedValue = match.group(1)!;
        final normalizedNumber = matchedValue.replaceAll(RegExp(r'[^0-9]'), '');
        return 'Lahan ${normalizedNumber.isNotEmpty ? normalizedNumber : matchedValue}';
      }
    }

    return null;
  }
}
