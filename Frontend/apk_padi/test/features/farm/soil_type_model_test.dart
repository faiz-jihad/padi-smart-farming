import 'package:flutter_test/flutter_test.dart';
import 'package:padi/features/farm/data/models/soil_type_model.dart';

void main() {
  group('SoilTypeModel', () {
    test('fromJson creates model correctly with string and bool values', () {
      final json = {
        'id': 1,
        'name': 'Lempung Berpasir / Loam',
        'code': 'loam',
        'description': 'Ideal untuk padi sawah',
        'is_active': true,
      };

      final model = SoilTypeModel.fromJson(json);

      expect(model.id, 1);
      expect(model.name, 'Lempung Berpasir / Loam');
      expect(model.code, 'loam');
      expect(model.description, 'Ideal untuk padi sawah');
      expect(model.isActive, isTrue);
    });

    test('fromJson handles string id and integer is_active gracefully', () {
      final json = {
        'id': '2',
        'name': 'Regosol Gunung',
        'code': 'regosol',
        'description': null,
        'is_active': 1,
      };

      final model = SoilTypeModel.fromJson(json);

      expect(model.id, 2);
      expect(model.name, 'Regosol Gunung');
      expect(model.code, 'regosol');
      expect(model.description, isNull);
      expect(model.isActive, isTrue);
    });

    test('toJson converts model back to map', () {
      const model = SoilTypeModel(
        id: 3,
        name: 'Gambut / Peat',
        code: 'peat',
        description: 'Lahan rawa',
        isActive: true,
      );

      final json = model.toJson();

      expect(json['id'], 3);
      expect(json['name'], 'Gambut / Peat');
      expect(json['code'], 'peat');
      expect(json['description'], 'Lahan rawa');
      expect(json['is_active'], isTrue);
    });
  });
}
