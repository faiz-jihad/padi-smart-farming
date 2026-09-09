import 'package:flutter_test/flutter_test.dart';
import 'package:padi/core/voice/intent_resolver.dart';
import 'package:padi/core/voice/voice_intent.dart';

void main() {
  const resolver = IntentResolver.instance;

  group('IntentResolver — Section 72 Test Cases', () {
    test('startPlantCheck intents', () {
      expect(resolver.resolve('Periksa tanaman').intent, VoiceIntent.startPlantCheck);
      expect(resolver.resolve('cek daun').intent, VoiceIntent.startPlantCheck);
      expect(resolver.resolve('tanaman saya sakit').intent, VoiceIntent.startPlantCheck);
      expect(resolver.resolve('tolong periksa padi').intent, VoiceIntent.startPlantCheck);
      expect(resolver.resolve('scan daun').intent, VoiceIntent.startPlantCheck);
      // Bahasa Jawa dasar
      expect(resolver.resolve('priksa godhong').intent, VoiceIntent.startPlantCheck);
      expect(resolver.resolve('godhong lara').intent, VoiceIntent.startPlantCheck);
    });

    test('takePlantPhoto intents', () {
      expect(resolver.resolve('ambil foto').intent, VoiceIntent.takePlantPhoto);
      expect(resolver.resolve('jepret').intent, VoiceIntent.takePlantPhoto);
      expect(resolver.resolve('foto sekarang').intent, VoiceIntent.takePlantPhoto);
      expect(resolver.resolve('jupuk foto').intent, VoiceIntent.takePlantPhoto);
    });

    test('retakePhoto intents', () {
      expect(resolver.resolve('foto ulang').intent, VoiceIntent.retakePhoto);
      expect(resolver.resolve('ulangi foto').intent, VoiceIntent.retakePhoto);
      expect(resolver.resolve('ganti foto').intent, VoiceIntent.retakePhoto);
    });

    test('analyzePlantImage intents', () {
      expect(resolver.resolve('periksa foto ini').intent, VoiceIntent.analyzePlantImage);
      expect(resolver.resolve('analisis').intent, VoiceIntent.analyzePlantImage);
      expect(resolver.resolve('proses').intent, VoiceIntent.analyzePlantImage);
      expect(resolver.resolve('ya').intent, VoiceIntent.analyzePlantImage);
    });

    test('readDiagnosis intents', () {
      expect(resolver.resolve('bacakan hasilnya').intent, VoiceIntent.readDiagnosis);
      expect(resolver.resolve('bacakan hasil').intent, VoiceIntent.readDiagnosis);
      expect(resolver.resolve('tanaman saya sakit apa').intent, VoiceIntent.readDiagnosis);
      expect(resolver.resolve('wacakno hasil').intent, VoiceIntent.readDiagnosis);
    });

    test('readRecommendation intents', () {
      expect(resolver.resolve('bagaimana cara mengatasinya').intent, VoiceIntent.readRecommendation);
      expect(resolver.resolve('apa obatnya').intent, VoiceIntent.readRecommendation);
      expect(resolver.resolve('obatnya apa').intent, VoiceIntent.readRecommendation);
      expect(resolver.resolve('cara mengatasi').intent, VoiceIntent.readRecommendation);
      expect(resolver.resolve('kepiye carane').intent, VoiceIntent.readRecommendation);
    });

    test('escalateToPpl intents', () {
      expect(resolver.resolve('tanyakan ke penyuluh').intent, VoiceIntent.escalateToPpl);
      expect(resolver.resolve('hubungi ppl').intent, VoiceIntent.escalateToPpl);
      expect(resolver.resolve('konsultasi ppl').intent, VoiceIntent.escalateToPpl);
      expect(resolver.resolve('takon penyuluh').intent, VoiceIntent.escalateToPpl);
    });

    test('checkDiseaseWarning intents', () {
      expect(resolver.resolve('ada penyakit di sekitar').intent, VoiceIntent.checkDiseaseWarning);
      expect(resolver.resolve('cek penyakit sekitar').intent, VoiceIntent.checkDiseaseWarning);
      expect(resolver.resolve('radar penyakit').intent, VoiceIntent.checkDiseaseWarning);
      expect(resolver.resolve('ada peringatan').intent, VoiceIntent.checkDiseaseWarning);
    });

    test('getDailyPriority intents', () {
      expect(resolver.resolve('prioritas hari ini').intent, VoiceIntent.getDailyPriority);
      expect(resolver.resolve('apa yang harus saya lakukan hari ini').intent, VoiceIntent.getDailyPriority);
      expect(resolver.resolve('kegiatan hari ini').intent, VoiceIntent.getDailyPriority);
    });

    test('getFarmWeather intents', () {
      expect(resolver.resolve('apakah hari ini hujan').intent, VoiceIntent.getFarmWeather);
      expect(resolver.resolve('cuaca hari ini').intent, VoiceIntent.getFarmWeather);
      expect(resolver.resolve('kapan waktu yang bagus untuk menyemprot').intent, VoiceIntent.getFarmWeather);
    });

    test('recordActivity intents and entity extraction', () {
      final res1 = resolver.resolve('catat hari ini saya memupuk');
      expect(res1.intent, VoiceIntent.recordActivity);
      expect(res1.entityText, 'Pemupukan');

      final res2 = resolver.resolve('saya baru menyemprot');
      expect(res2.intent, VoiceIntent.recordActivity);
      expect(res2.entityText, 'Penyemprotan');

      final res3 = resolver.resolve('saya baru menyiram');
      expect(res3.intent, VoiceIntent.recordActivity);
      expect(res3.entityText, 'Pengairan');
    });

    test('openMarketplace intents', () {
      expect(resolver.resolve('buka pasar').intent, VoiceIntent.openMarketplace);
      expect(resolver.resolve('lihat harga gabah').intent, VoiceIntent.openMarketplace);
      expect(resolver.resolve('saya mau jual').intent, VoiceIntent.openMarketplace);
    });

    test('Confidence thresholding logic', () {
      final highConf = resolver.resolve('periksa tanaman', confidence: 0.95);
      expect(highConf.isHighConfidence, isTrue);
      expect(highConf.requiresConfirmation, isFalse);

      final medConf = resolver.resolve('periksa tanaman', confidence: 0.65);
      expect(medConf.isHighConfidence, isFalse);
      expect(medConf.requiresConfirmation, isTrue);

      final lowConf = resolver.resolve('periksa tanaman', confidence: 0.40);
      expect(lowConf.isHighConfidence, isFalse);
      expect(lowConf.requiresConfirmation, isFalse);
    });

    test('Unknown intent handling', () {
      final unknown = resolver.resolve('nonton bioskop yuk');
      expect(unknown.intent, VoiceIntent.unknown);
    });
  });
}
