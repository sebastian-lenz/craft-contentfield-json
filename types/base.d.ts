declare namespace contentfield {
  interface Element {
    title: string|null;
    uid: string;
  }

  interface Asset extends Element {
    volume: string;
  }

  interface AssetTransform {
    height: number;
    url: string;
    width: number;
  }

  interface AssetTransformMap {
    [name: string]: AssetTransform;
  }

  interface Category extends Element {
    group: string;
  }

  interface Entry extends Element {
    entryType: string;
    section: string;
  }

  interface GlobalSet extends Element {
    globalSet: string;
  }

  interface Instance {
    uid: string;
    type: string;
  }

  interface MatrixBlock extends Element {
    type: string;
  }

  interface Layout<TValue> {
    columns: Array<LayoutColumn<TValue>>;
    preset: string|null;
  }

  interface LayoutColumn<TValue> {
    className: string;
    value: TValue;
  }

  interface AnchorDefinition {
    id: string;
    title: string;
  }

  interface Link {
    type: string;
    newWindow: boolean;
    url: string;
  }

  interface Point {
    x: number;
    y: number;
  }
}
