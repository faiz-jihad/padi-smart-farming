import 'package:flutter_test/flutter_test.dart';
import 'package:padi/core/localization/app_language.dart';

void main() {
  group('AppLanguage Tests', () {
    test('Fallback to id when code is invalid or null', () {
      expect(AppLanguage.fromCode(null), AppLanguage.id);
      expect(AppLanguage.fromCode('unknown'), AppLanguage.id);
      expect(AppLanguage.fromCode(''), AppLanguage.id);
    });

    test('Loads correct enum from valid codes', () {
      expect(AppLanguage.fromCode('id'), AppLanguage.id);
      expect(AppLanguage.fromCode('jv'), AppLanguage.jv);
      expect(AppLanguage.fromCode('en'), AppLanguage.en);
    });

    test('Locale codes match standard naming', () {
      expect(AppLanguage.id.locale.languageCode, 'id');
      expect(AppLanguage.jv.locale.languageCode, 'jv');
      expect(AppLanguage.en.locale.languageCode, 'en');
    });
  });

  group('AppStrings Translations Tests', () {
    test('Indonesian strings are clear and farmer-friendly', () {
      const s = AppStrings(AppLanguage.id);
      expect(s.navHome, 'Beranda');
      expect(s.navFarms, 'Lahan');
      expect(s.quickActions, 'Aksi cepat');
      expect(s.checkCrops, 'Periksa Tanaman');
      expect(s.addFarm, 'Tambah Lahan');
      expect(s.todayActivities, 'Aktivitas hari ini');
      expect(s.noActivitiesToday, 'Belum ada aktivitas hari ini');
      expect(s.helloUser('Faiz'), 'Halo, Faiz 👋');
      expect(s.activeFarmsCount(3), '3 lahan aktif');
      expect(s.cropAgeDays(45), 'Hari ke-45');
    });

    test('Javanese strings use natural daily farmer terms', () {
      const s = AppStrings(AppLanguage.jv);
      expect(s.navHome, 'Beranda');
      expect(s.navFarms, 'Sawah');
      expect(s.quickActions, 'Aksi cepet');
      expect(s.checkCrops, 'Priksa Tanduran');
      expect(s.addFarm, 'Tambah Sawah');
      expect(s.todayActivities, 'Kegiatan dina iki');
      expect(s.noActivitiesToday, 'Dina iki durung ana kegiatan');
      expect(s.farmConditionQuestion, 'Kepiye kondisi sawahe dina iki?');
      expect(s.seeFarmCondition, 'Delok kondisi sawah');
      expect(s.signOut, 'Metu');
      expect(s.activeFarmsCount(2), '2 sawah aktif');
      expect(s.cropAgeDays(30), 'Dina ke-30');
    });

    test('English strings are concise and professional', () {
      const s = AppStrings(AppLanguage.en);
      expect(s.navHome, 'Home');
      expect(s.navFarms, 'Farm');
      expect(s.quickActions, 'Quick Actions');
      expect(s.checkCrops, 'Check Crops');
      expect(s.addFarm, 'Add Farm');
      expect(s.todayActivities, "Today's Activities");
      expect(s.noActivitiesToday, 'No activities recorded today');
      expect(s.signOut, 'Sign Out');
      expect(s.activeFarmsCount(1), '1 active farm');
      expect(s.activeFarmsCount(5), '5 active farms');
      expect(s.cropAgeDays(12), 'Day 12');
    });

    test('Backend Enum & Growth Phase Mapping works for all locales', () {
      for (final lang in AppLanguage.values) {
        final s = AppStrings(lang);
        expect(s.mapCropHealth('healthy').isNotEmpty, isTrue);
        expect(s.mapCropHealth('warning').isNotEmpty, isTrue);
        expect(s.mapGrowthPhase(10).isNotEmpty, isTrue);
        expect(s.mapGrowthPhase(35).isNotEmpty, isTrue);
        expect(s.mapGrowthPhase(70).isNotEmpty, isTrue);
        expect(s.mapGrowthPhase(100).isNotEmpty, isTrue);
      }
    });

    test('Friendly error messages do not leak technical stack', () {
      for (final lang in AppLanguage.values) {
        final s = AppStrings(lang);
        final err = s.friendlyErrorMessage;
        expect(err.contains('SQL'), isFalse);
        expect(err.contains('Dio'), isFalse);
        expect(err.contains('500'), isFalse);
        expect(err.isNotEmpty, isTrue);
      }
    });
  });
}
