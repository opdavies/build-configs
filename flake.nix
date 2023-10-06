{
  inputs.nixpkgs.url = "github:NixOS/nixpkgs/nixos-23.05";

  outputs = inputs@{ flake-parts, ... }:
    flake-parts.lib.mkFlake { inherit inputs; } {
      systems = [ "x86_64-linux" ];

      perSystem = { config, self', inputs', pkgs, system, ... }: {
        devShells.default = pkgs.mkShell {
          buildInputs = with pkgs; [ php82 php82Packages.composer ];
        };

        packages.default = pkgs.runCommand "default" { src = "./."; } ''
          mkdir -p $out
          cp ./build-configs $out
        '';
      };
    };
}
